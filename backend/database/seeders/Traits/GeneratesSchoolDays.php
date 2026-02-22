<?php

namespace Database\Seeders\Traits;

use Carbon\Carbon;

trait GeneratesSchoolDays
{
    /** @return list<string> */
    private function generateSchoolDays(int $year, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate) : Carbon::create($year, 2, 10);
        $end = $endDate ? Carbon::parse($endDate) : Carbon::create($year, 12, 18);
        $recessStart = Carbon::create($year, 7, 1);
        $recessEnd = Carbon::create($year, 8, 3);
        $holidays = $this->buildHolidays($year);

        $days = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            if (
                $current->isWeekday()
                && ! $current->between($recessStart, $recessEnd)
                && ! in_array($current->format('Y-m-d'), $holidays, true)
            ) {
                $days[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        return $days;
    }

    /** @return list<string> */
    private function buildHolidays(int $year): array
    {
        $easter = $this->calculateEaster($year);

        return [
            $easter->copy()->subDays(48)->format('Y-m-d'),
            $easter->copy()->subDays(47)->format('Y-m-d'),
            $easter->copy()->subDays(46)->format('Y-m-d'),
            $easter->copy()->subDays(2)->format('Y-m-d'),
            $easter->copy()->addDays(60)->format('Y-m-d'),
            "$year-04-21",
            "$year-05-01",
            "$year-09-07",
            "$year-10-12",
            "$year-11-02",
            "$year-11-15",
            "$year-11-20",
            "$year-12-25",
        ];
    }

    private function calculateEaster(int $year): Carbon
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($year, $month, $day);
    }
}
