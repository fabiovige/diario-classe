<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassScheduleSeeder extends Seeder
{
    private const BATCH_SIZE = 500;

    private const DAYS = [1, 2, 3, 4, 5];

    private const WEEKLY_HOURS_EARLY_CHILDHOOD = [
        'EF01' => 5, 'EF02' => 5, 'EF03' => 5, 'EF04' => 5, 'EF05' => 5,
    ];

    private const WEEKLY_HOURS_ELEMENTARY_EARLY = [
        'LP' => 6, 'MAT' => 6, 'CIE' => 3, 'HIS' => 2, 'GEO' => 2,
        'ER' => 1, 'ART' => 2, 'EDF' => 2, 'ING' => 1,
    ];

    private const WEEKLY_HOURS_ELEMENTARY_LATE = [
        'LP' => 6, 'MAT' => 6, 'CIE' => 3, 'HIS' => 2, 'GEO' => 2,
        'ER' => 1, 'ART' => 2, 'EDF' => 2, 'ING' => 1,
    ];

    public function run(): void
    {
        $classGroups = DB::table('class_groups')
            ->join('grade_levels', 'grade_levels.id', '=', 'class_groups.grade_level_id')
            ->select('class_groups.id', 'class_groups.shift_id', 'grade_levels.type')
            ->get();

        $timeSlotsByShift = DB::table('time_slots')
            ->where('type', 'class')
            ->orderBy('shift_id')
            ->orderBy('number')
            ->get()
            ->groupBy('shift_id');

        $components = DB::table('curricular_components')->where('active', true)->pluck('id', 'code');
        $experienceFields = DB::table('experience_fields')->where('active', true)->pluck('id', 'code');

        $now = now()->toDateTimeString();
        $batch = [];
        $total = 0;
        $processed = 0;

        foreach ($classGroups as $cg) {
            $slots = $timeSlotsByShift->get($cg->shift_id);
            if (! $slots || $slots->isEmpty()) {
                continue;
            }

            $slotIds = $slots->pluck('id')->values()->toArray();
            $slotsPerDay = count($slotIds);

            $assignments = DB::table('teacher_assignments')
                ->where('class_group_id', $cg->id)
                ->where('active', true)
                ->get();

            if ($assignments->isEmpty()) {
                continue;
            }

            $weeklyHours = $this->getWeeklyHours($cg->type);
            $schedule = $this->buildWeeklySchedule($assignments, $weeklyHours, $components, $experienceFields, $slotsPerDay);

            foreach ($schedule as $dayIndex => $daySlots) {
                $day = self::DAYS[$dayIndex];
                foreach ($daySlots as $slotIndex => $assignmentId) {
                    if ($assignmentId === null) {
                        continue;
                    }
                    if (! isset($slotIds[$slotIndex])) {
                        continue;
                    }

                    $batch[] = [
                        'teacher_assignment_id' => $assignmentId,
                        'time_slot_id' => $slotIds[$slotIndex],
                        'day_of_week' => $day,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            $processed++;

            if (count($batch) >= self::BATCH_SIZE) {
                DB::table('class_schedules')->insert($batch);
                $total += count($batch);
                $batch = [];
            }

            if ($processed % 200 === 0) {
                $cgCount = $classGroups->count();
                $this->command->info("  ClassSchedules: $processed/$cgCount turmas...");
            }
        }

        if (! empty($batch)) {
            DB::table('class_schedules')->insert($batch);
            $total += count($batch);
        }

        $cgCount = $classGroups->count();
        $this->command->info("  ClassSchedules: $total aulas agendadas para $cgCount turmas.");
    }

    /** @return array<string, int> */
    private function getWeeklyHours(string $type): array
    {
        return match ($type) {
            'early_childhood' => self::WEEKLY_HOURS_EARLY_CHILDHOOD,
            'elementary_early' => self::WEEKLY_HOURS_ELEMENTARY_EARLY,
            'elementary_late' => self::WEEKLY_HOURS_ELEMENTARY_LATE,
            default => [],
        };
    }

    /**
     * @param \Illuminate\Support\Collection<int, \stdClass> $assignments
     * @param array<string, int> $weeklyHours
     * @param \Illuminate\Support\Collection<string, int> $components
     * @param \Illuminate\Support\Collection<string, int> $experienceFields
     * @return array<int, array<int, int|null>>
     */
    private function buildWeeklySchedule($assignments, array $weeklyHours, $components, $experienceFields, int $slotsPerDay): array
    {
        $grid = array_fill(0, 5, array_fill(0, $slotsPerDay, null));

        $assignmentsByCode = [];
        foreach ($assignments as $assignment) {
            $code = $this->resolveCode($assignment, $components, $experienceFields);
            if ($code !== null) {
                $assignmentsByCode[$code] = $assignment->id;
            }
        }

        $pool = [];
        foreach ($weeklyHours as $code => $hours) {
            if (! isset($assignmentsByCode[$code])) {
                continue;
            }
            for ($i = 0; $i < $hours; $i++) {
                $pool[] = $assignmentsByCode[$code];
            }
        }

        shuffle($pool);

        $poolIndex = 0;
        for ($day = 0; $day < 5; $day++) {
            for ($slot = 0; $slot < $slotsPerDay; $slot++) {
                if ($poolIndex >= count($pool)) {
                    break 2;
                }
                $grid[$day][$slot] = $pool[$poolIndex];
                $poolIndex++;
            }
        }

        return $grid;
    }

    /**
     * @param \Illuminate\Support\Collection<string, int> $components
     * @param \Illuminate\Support\Collection<string, int> $experienceFields
     */
    private function resolveCode(\stdClass $assignment, $components, $experienceFields): ?string
    {
        if ($assignment->curricular_component_id) {
            $key = $components->search($assignment->curricular_component_id);

            return $key !== false ? (string) $key : null;
        }

        if ($assignment->experience_field_id) {
            $key = $experienceFields->search($assignment->experience_field_id);

            return $key !== false ? (string) $key : null;
        }

        return null;
    }
}
