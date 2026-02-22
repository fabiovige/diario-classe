<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeSlotSeeder extends Seeder
{
    private const MORNING_SLOTS = [
        ['number' => 1, 'start_time' => '07:00', 'end_time' => '07:50', 'type' => 'class'],
        ['number' => 2, 'start_time' => '07:50', 'end_time' => '08:40', 'type' => 'class'],
        ['number' => 3, 'start_time' => '08:40', 'end_time' => '09:00', 'type' => 'break'],
        ['number' => 4, 'start_time' => '09:00', 'end_time' => '09:50', 'type' => 'class'],
        ['number' => 5, 'start_time' => '09:50', 'end_time' => '10:40', 'type' => 'class'],
        ['number' => 6, 'start_time' => '10:40', 'end_time' => '11:30', 'type' => 'class'],
    ];

    private const AFTERNOON_SLOTS = [
        ['number' => 1, 'start_time' => '13:00', 'end_time' => '13:50', 'type' => 'class'],
        ['number' => 2, 'start_time' => '13:50', 'end_time' => '14:40', 'type' => 'class'],
        ['number' => 3, 'start_time' => '14:40', 'end_time' => '15:00', 'type' => 'break'],
        ['number' => 4, 'start_time' => '15:00', 'end_time' => '15:50', 'type' => 'class'],
        ['number' => 5, 'start_time' => '15:50', 'end_time' => '16:40', 'type' => 'class'],
        ['number' => 6, 'start_time' => '16:40', 'end_time' => '17:30', 'type' => 'class'],
    ];

    public function run(): void
    {
        $shifts = DB::table('shifts')->get();
        $now = now()->toDateTimeString();
        $batch = [];

        foreach ($shifts as $shift) {
            $slots = $shift->name === 'morning' ? self::MORNING_SLOTS : self::AFTERNOON_SLOTS;

            foreach ($slots as $slot) {
                $batch[] = [
                    'shift_id' => $shift->id,
                    'number' => $slot['number'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'type' => $slot['type'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($batch)) {
            DB::table('time_slots')->insert($batch);
        }

        $this->command->info('  TimeSlots: ' . count($batch) . ' horarios criados.');
    }
}
