<?php

namespace App\Modules\Attendance\Presentation\Controllers;

use App\Modules\Attendance\Application\DTOs\CreateAbsenceJustificationDTO;
use App\Modules\Attendance\Application\DTOs\CreateAttendanceConfigDTO;
use App\Modules\Attendance\Application\DTOs\RecordBulkAttendanceDTO;
use App\Modules\Attendance\Application\UseCases\ApproveAbsenceJustificationUseCase;
use App\Modules\Attendance\Application\UseCases\CreateAttendanceConfigUseCase;
use App\Modules\Attendance\Application\UseCases\JustifyAbsenceUseCase;
use App\Modules\Attendance\Application\UseCases\RecordBulkAttendanceUseCase;
use App\Modules\Attendance\Domain\Entities\AbsenceJustification;
use App\Modules\Attendance\Domain\Entities\AttendanceConfig;
use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use App\Modules\Attendance\Domain\Services\AttendanceAlertChecker;
use App\Modules\Attendance\Domain\Services\FrequencyCalculator;
use App\Modules\Attendance\Presentation\Requests\CreateAbsenceJustificationRequest;
use App\Modules\Attendance\Presentation\Requests\CreateAttendanceConfigRequest;
use App\Modules\Attendance\Presentation\Requests\RecordBulkAttendanceRequest;
use App\Modules\Attendance\Presentation\Resources\AbsenceJustificationResource;
use App\Modules\Attendance\Presentation\Resources\AttendanceConfigResource;
use App\Modules\Attendance\Presentation\Resources\AttendanceRecordResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends ApiController
{
    public function bulkRecord(RecordBulkAttendanceRequest $request, RecordBulkAttendanceUseCase $useCase): JsonResponse
    {
        $records = $useCase->execute(new RecordBulkAttendanceDTO(
            classGroupId: $request->validated('class_group_id'),
            teacherAssignmentId: $request->validated('teacher_assignment_id'),
            date: $request->validated('date'),
            records: $request->validated('records'),
        ));

        return $this->created(AttendanceRecordResource::collection($records));
    }

    public function byClass(Request $request, int $classGroupId): JsonResponse
    {
        $records = AttendanceRecord::with('student')
            ->where('class_group_id', $classGroupId)
            ->when($request->query('teacher_assignment_id'), fn ($q, $id) => $q->where('teacher_assignment_id', $id))
            ->when($request->query('date'), fn ($q, $date) => $q->where('date', $date))
            ->when($request->query('date_from'), fn ($q, $date) => $q->where('date', '>=', $date))
            ->when($request->query('date_to'), fn ($q, $date) => $q->where('date', '<=', $date))
            ->orderBy('date')
            ->orderBy('student_id')
            ->paginate($request->query('per_page', 50));

        return $this->success(AttendanceRecordResource::collection($records)->response()->getData(true));
    }

    public function byStudent(Request $request, int $studentId): JsonResponse
    {
        $records = AttendanceRecord::with('classGroup')
            ->where('student_id', $studentId)
            ->when($request->query('class_group_id'), fn ($q, $id) => $q->where('class_group_id', $id))
            ->orderByDesc('date')
            ->paginate($request->query('per_page', 30));

        return $this->success(AttendanceRecordResource::collection($records)->response()->getData(true));
    }

    public function studentFrequency(Request $request, int $studentId, FrequencyCalculator $calculator): JsonResponse
    {
        $frequency = $calculator->calculate(
            studentId: $studentId,
            classGroupId: (int) $request->query('class_group_id'),
            teacherAssignmentId: $request->query('teacher_assignment_id') ? (int) $request->query('teacher_assignment_id') : null,
            startDate: $request->query('start_date'),
            endDate: $request->query('end_date'),
        );

        return $this->success($frequency);
    }

    public function alerts(int $studentId, Request $request, AttendanceAlertChecker $checker): JsonResponse
    {
        $result = $checker->check(
            studentId: $studentId,
            classGroupId: (int) $request->query('class_group_id'),
            schoolId: (int) $request->query('school_id'),
            academicYearId: (int) $request->query('academic_year_id'),
        );

        return $this->success($result);
    }

    public function storeJustification(CreateAbsenceJustificationRequest $request, JustifyAbsenceUseCase $useCase): JsonResponse
    {
        $justification = $useCase->execute(new CreateAbsenceJustificationDTO(
            studentId: $request->validated('student_id'),
            startDate: $request->validated('start_date'),
            endDate: $request->validated('end_date'),
            reason: $request->validated('reason'),
            documentPath: $request->validated('document_path'),
        ));

        return $this->created(new AbsenceJustificationResource($justification));
    }

    public function approveJustification(int $id, ApproveAbsenceJustificationUseCase $useCase): JsonResponse
    {
        $justification = $useCase->execute($id);

        return $this->success(new AbsenceJustificationResource($justification));
    }

    public function indexConfig(Request $request): JsonResponse
    {
        $configs = AttendanceConfig::query()
            ->when($request->query('school_id'), fn ($q, $id) => $q->where('school_id', $id))
            ->when($request->query('academic_year_id'), fn ($q, $id) => $q->where('academic_year_id', $id))
            ->paginate($request->query('per_page', 15));

        return $this->success(AttendanceConfigResource::collection($configs)->response()->getData(true));
    }

    public function storeConfig(CreateAttendanceConfigRequest $request, CreateAttendanceConfigUseCase $useCase): JsonResponse
    {
        $config = $useCase->execute(new CreateAttendanceConfigDTO(
            schoolId: $request->validated('school_id'),
            academicYearId: $request->validated('academic_year_id'),
            consecutiveAbsencesAlert: $request->validated('consecutive_absences_alert', 5),
            monthlyAbsencesAlert: $request->validated('monthly_absences_alert', 10),
            periodAbsencePercentageAlert: $request->validated('period_absence_percentage_alert', 25.00),
            annualMinimumFrequency: $request->validated('annual_minimum_frequency', 75.00),
        ));

        return $this->created(new AttendanceConfigResource($config));
    }
}
