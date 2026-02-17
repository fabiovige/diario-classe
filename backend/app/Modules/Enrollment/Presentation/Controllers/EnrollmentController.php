<?php

namespace App\Modules\Enrollment\Presentation\Controllers;

use App\Modules\Enrollment\Application\DTOs\AssignToClassDTO;
use App\Modules\Enrollment\Application\DTOs\CreateEnrollmentDTO;
use App\Modules\Enrollment\Application\UseCases\AssignToClassUseCase;
use App\Modules\Enrollment\Application\UseCases\CreateEnrollmentUseCase;
use App\Modules\Enrollment\Application\UseCases\TransferEnrollmentUseCase;
use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\Enrollment\Presentation\Requests\AssignToClassRequest;
use App\Modules\Enrollment\Presentation\Requests\CreateEnrollmentRequest;
use App\Modules\Enrollment\Presentation\Requests\TransferEnrollmentRequest;
use App\Modules\Enrollment\Presentation\Resources\ClassAssignmentResource;
use App\Modules\Enrollment\Presentation\Resources\EnrollmentResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $enrollments = Enrollment::with(['student', 'academicYear', 'school', 'classAssignments.classGroup'])
            ->when($request->query('school_id'), fn ($q, $id) => $q->where('school_id', $id))
            ->when($request->query('academic_year_id'), fn ($q, $id) => $q->where('academic_year_id', $id))
            ->when($request->query('student_id'), fn ($q, $id) => $q->where('student_id', $id))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('enrollment_date')
            ->paginate($request->query('per_page', 15));

        return $this->success(EnrollmentResource::collection($enrollments)->response()->getData(true));
    }

    public function store(CreateEnrollmentRequest $request, CreateEnrollmentUseCase $useCase): JsonResponse
    {
        $enrollment = $useCase->execute(new CreateEnrollmentDTO(
            studentId: $request->validated('student_id'),
            academicYearId: $request->validated('academic_year_id'),
            schoolId: $request->validated('school_id'),
            enrollmentDate: $request->validated('enrollment_date'),
            enrollmentNumber: $request->validated('enrollment_number'),
        ));

        return $this->created(new EnrollmentResource($enrollment->load(['student', 'academicYear', 'school', 'movements'])));
    }

    public function show(int $id): JsonResponse
    {
        $enrollment = Enrollment::with(['student', 'academicYear', 'school', 'classAssignments.classGroup', 'movements'])->findOrFail($id);

        return $this->success(new EnrollmentResource($enrollment));
    }

    public function assignToClass(AssignToClassRequest $request, int $enrollmentId, AssignToClassUseCase $useCase): JsonResponse
    {
        $assignment = $useCase->execute(new AssignToClassDTO(
            enrollmentId: $enrollmentId,
            classGroupId: $request->validated('class_group_id'),
            startDate: $request->validated('start_date'),
        ));

        return $this->created(new ClassAssignmentResource($assignment->load('classGroup')));
    }

    public function transfer(TransferEnrollmentRequest $request, int $enrollmentId, TransferEnrollmentUseCase $useCase): JsonResponse
    {
        $enrollment = $useCase->execute(
            enrollmentId: $enrollmentId,
            type: $request->validated('type'),
            date: $request->validated('movement_date'),
            reason: $request->validated('reason'),
        );

        return $this->success(new EnrollmentResource($enrollment->load(['student', 'movements'])));
    }

    public function movements(int $enrollmentId): JsonResponse
    {
        $enrollment = Enrollment::with('movements')->findOrFail($enrollmentId);

        return $this->success($enrollment->movements);
    }
}
