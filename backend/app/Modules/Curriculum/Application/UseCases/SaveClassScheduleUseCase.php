<?php

namespace App\Modules\Curriculum\Application\UseCases;

use App\Modules\Curriculum\Application\DTOs\SaveClassScheduleDTO;
use App\Modules\Curriculum\Domain\Entities\ClassSchedule;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Curriculum\Domain\Entities\TimeSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SaveClassScheduleUseCase
{
    /**
     * @return \Illuminate\Support\Collection<int, ClassSchedule>
     */
    public function execute(SaveClassScheduleDTO $dto): \Illuminate\Support\Collection
    {
        $assignment = TeacherAssignment::with('classGroup.shift')->findOrFail($dto->teacherAssignmentId);

        $this->validateSlotsMatchShift($assignment, $dto->slots);
        $this->validateNoClassGroupConflict($assignment, $dto->slots);
        $this->validateNoTeacherConflict($assignment, $dto->slots);

        return DB::transaction(function () use ($dto) {
            ClassSchedule::where('teacher_assignment_id', $dto->teacherAssignmentId)->delete();

            $schedules = collect();

            foreach ($dto->slots as $slot) {
                $schedules->push(ClassSchedule::create([
                    'teacher_assignment_id' => $dto->teacherAssignmentId,
                    'time_slot_id' => $slot['time_slot_id'],
                    'day_of_week' => $slot['day_of_week'],
                ]));
            }

            return $schedules;
        });
    }

    /**
     * @param  array<array{time_slot_id: int, day_of_week: int}>  $slots
     */
    private function validateSlotsMatchShift(TeacherAssignment $assignment, array $slots): void
    {
        $shiftId = $assignment->classGroup->shift_id;
        $timeSlotIds = array_unique(array_column($slots, 'time_slot_id'));

        $invalidCount = TimeSlot::whereIn('id', $timeSlotIds)
            ->where('shift_id', '!=', $shiftId)
            ->count();

        if ($invalidCount > 0) {
            throw ValidationException::withMessages([
                'slots' => 'Os horarios selecionados nao pertencem ao turno da turma.',
            ]);
        }
    }

    /**
     * @param  array<array{time_slot_id: int, day_of_week: int}>  $slots
     */
    private function validateNoClassGroupConflict(TeacherAssignment $assignment, array $slots): void
    {
        $classGroupId = $assignment->class_group_id;

        $sameClassAssignmentIds = TeacherAssignment::where('class_group_id', $classGroupId)
            ->where('id', '!=', $assignment->id)
            ->pluck('id');

        if ($sameClassAssignmentIds->isEmpty()) {
            return;
        }

        foreach ($slots as $slot) {
            $conflict = ClassSchedule::whereIn('teacher_assignment_id', $sameClassAssignmentIds)
                ->where('time_slot_id', $slot['time_slot_id'])
                ->where('day_of_week', $slot['day_of_week'])
                ->exists();

            if ($conflict) {
                $timeSlot = TimeSlot::find($slot['time_slot_id']);
                throw ValidationException::withMessages([
                    'slots' => "Conflito de turma: ja existe outra disciplina no horario {$timeSlot->start_time} ({$this->dayLabel($slot['day_of_week'])}).",
                ]);
            }
        }
    }

    /**
     * @param  array<array{time_slot_id: int, day_of_week: int}>  $slots
     */
    private function validateNoTeacherConflict(TeacherAssignment $assignment, array $slots): void
    {
        $teacherId = $assignment->teacher_id;

        $otherAssignmentIds = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('id', '!=', $assignment->id)
            ->pluck('id');

        if ($otherAssignmentIds->isEmpty()) {
            return;
        }

        foreach ($slots as $slot) {
            $conflict = ClassSchedule::whereIn('teacher_assignment_id', $otherAssignmentIds)
                ->where('time_slot_id', $slot['time_slot_id'])
                ->where('day_of_week', $slot['day_of_week'])
                ->exists();

            if ($conflict) {
                $timeSlot = TimeSlot::find($slot['time_slot_id']);
                throw ValidationException::withMessages([
                    'slots' => "Conflito de professor: o professor ja esta alocado em outra turma no horario {$timeSlot->start_time} ({$this->dayLabel($slot['day_of_week'])}).",
                ]);
            }
        }
    }

    private function dayLabel(int $day): string
    {
        $labels = [1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex'];

        return $labels[$day] ?? (string) $day;
    }
}
