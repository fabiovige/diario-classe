<?php

namespace App\Modules\PeriodClosing\Application\UseCases;

use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use Illuminate\Validation\ValidationException;

final class BulkTeacherClosePeriodUseCase
{
    public function __construct(
        private TeacherClosePeriodUseCase $teacherClose,
    ) {}

    /**
     * @return array{closed: array<int>, failed: array<array{id: int, errors: string}>}
     */
    public function execute(int $classGroupId, int $teacherAssignmentId): array
    {
        $closings = PeriodClosing::where('class_group_id', $classGroupId)
            ->where('teacher_assignment_id', $teacherAssignmentId)
            ->where('status', ClosingStatus::Pending)
            ->get();

        if ($closings->isEmpty()) {
            throw ValidationException::withMessages([
                'closings' => 'Nenhum fechamento pendente encontrado para esta turma e atribuicao.',
            ]);
        }

        $closed = [];
        $failed = [];

        foreach ($closings as $closing) {
            try {
                $this->teacherClose->execute($closing->id);
                $closed[] = $closing->id;
            } catch (ValidationException $e) {
                $messages = collect($e->errors())->flatten()->implode('; ');
                $failed[] = ['id' => $closing->id, 'errors' => $messages];
            }
        }

        return ['closed' => $closed, 'failed' => $failed];
    }
}
