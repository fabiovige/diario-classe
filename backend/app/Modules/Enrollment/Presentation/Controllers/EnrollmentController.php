<?php

namespace App\Modules\Enrollment\Presentation\Controllers;

use App\Modules\Enrollment\Application\DTOs\AssignToClassDTO;
use App\Modules\Enrollment\Application\DTOs\CreateEnrollmentDTO;
use App\Modules\Enrollment\Application\UseCases\AssignToClassUseCase;
use App\Modules\Enrollment\Application\UseCases\CreateEnrollmentUseCase;
use App\Modules\Enrollment\Application\UseCases\TransferEnrollmentUseCase;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\Enrollment\Domain\Entities\EnrollmentDocument;
use App\Modules\Enrollment\Domain\Enums\ClassAssignmentStatus;
use App\Modules\Enrollment\Domain\Enums\DocumentStatus;
use App\Modules\Enrollment\Domain\Enums\DocumentType;
use App\Modules\Enrollment\Domain\Enums\EnrollmentStatus;
use App\Modules\Enrollment\Presentation\Requests\AssignToClassRequest;
use App\Modules\Enrollment\Presentation\Requests\CreateEnrollmentRequest;
use App\Modules\Enrollment\Presentation\Requests\ReviewDocumentRequest;
use App\Modules\Enrollment\Presentation\Requests\TransferEnrollmentRequest;
use App\Modules\Enrollment\Presentation\Requests\UpdateClassAssignmentRequest;
use App\Modules\Enrollment\Presentation\Requests\UploadDocumentRequest;
use App\Modules\Enrollment\Presentation\Resources\ClassAssignmentResource;
use App\Modules\Enrollment\Presentation\Resources\EnrollmentDocumentResource;
use App\Modules\Enrollment\Presentation\Resources\EnrollmentMovementResource;
use App\Modules\Enrollment\Presentation\Resources\EnrollmentResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnrollmentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $enrollments = Enrollment::with(['student', 'academicYear', 'school', 'classAssignments.classGroup.gradeLevel', 'classAssignments.classGroup.shift'])
            ->when($request->query('search'), fn ($q, $search) => $q->whereHas('student', fn ($sub) => $sub->where('name', 'like', "%{$search}%")))
            ->when($request->query('school_id'), fn ($q, $id) => $q->where('school_id', $id))
            ->when($request->query('academic_year_id'), fn ($q, $id) => $q->where('academic_year_id', $id))
            ->when($request->query('student_id'), fn ($q, $id) => $q->where('student_id', $id))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('class_group_id'), fn ($q, $id) => $q->whereHas('classAssignments', fn ($sub) => $sub->where('class_group_id', $id)))
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
            enrollmentType: $request->validated('enrollment_type', 'new_enrollment'),
            enrollmentNumber: $request->validated('enrollment_number'),
        ));

        return $this->created(new EnrollmentResource($enrollment->load(['student', 'academicYear', 'school', 'movements'])));
    }

    public function show(int $id): JsonResponse
    {
        $enrollment = Enrollment::with(['student', 'academicYear', 'school', 'classAssignments.classGroup.gradeLevel', 'classAssignments.classGroup.shift', 'movements.originSchool', 'movements.destinationSchool', 'documents'])->findOrFail($id);

        return $this->success(new EnrollmentResource($enrollment));
    }

    public function assignToClass(AssignToClassRequest $request, int $enrollmentId, AssignToClassUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute(new AssignToClassDTO(
            enrollmentId: $enrollmentId,
            classGroupId: $request->validated('class_group_id'),
            startDate: $request->validated('start_date'),
        ));

        $data = (new ClassAssignmentResource($result->assignment->load('classGroup.gradeLevel', 'classGroup.shift')))->toArray(request());

        if ($result->warnings !== []) {
            $data['warnings'] = $result->warnings;
        }

        return $this->created($data);
    }

    public function transfer(TransferEnrollmentRequest $request, int $enrollmentId, TransferEnrollmentUseCase $useCase): JsonResponse
    {
        $enrollment = $useCase->execute(
            enrollmentId: $enrollmentId,
            type: $request->validated('type'),
            date: $request->validated('movement_date'),
            reason: $request->validated('reason'),
            originSchoolId: $request->validated('origin_school_id'),
            destinationSchoolId: $request->validated('destination_school_id'),
        );

        return $this->success(new EnrollmentResource($enrollment->load(['student', 'movements.originSchool', 'movements.destinationSchool'])));
    }

    public function movements(int $enrollmentId): JsonResponse
    {
        $enrollment = Enrollment::with(['movements.originSchool', 'movements.destinationSchool'])->findOrFail($enrollmentId);

        return $this->success(EnrollmentMovementResource::collection($enrollment->movements));
    }

    public function destroy(int $id): JsonResponse
    {
        $enrollment = Enrollment::findOrFail($id);

        if (! $enrollment->isActive()) {
            return $this->error('Apenas matriculas ativas podem ser canceladas', 422);
        }

        $enrollment->update([
            'status' => EnrollmentStatus::Cancelled,
            'exit_date' => now()->toDateString(),
        ]);

        $enrollment->classAssignments()
            ->where('status', ClassAssignmentStatus::Active)
            ->update([
                'status' => ClassAssignmentStatus::Cancelled,
                'end_date' => now()->toDateString(),
            ]);

        return $this->noContent();
    }

    public function reactivate(int $id): JsonResponse
    {
        $enrollment = Enrollment::findOrFail($id);

        if ($enrollment->status !== EnrollmentStatus::Cancelled) {
            return $this->error('Apenas matriculas canceladas podem ser reativadas', 422);
        }

        $enrollment->update([
            'status' => EnrollmentStatus::Active,
            'exit_date' => null,
        ]);

        return $this->success(new EnrollmentResource($enrollment->load(['student', 'academicYear', 'school'])));
    }

    public function updateClassAssignment(UpdateClassAssignmentRequest $request, int $enrollmentId, int $classAssignmentId): JsonResponse
    {
        Enrollment::findOrFail($enrollmentId);

        $assignment = ClassAssignment::where('enrollment_id', $enrollmentId)->findOrFail($classAssignmentId);
        $assignment->update($request->validated());

        return $this->success(new ClassAssignmentResource($assignment->load('classGroup.gradeLevel', 'classGroup.shift')));
    }

    public function destroyClassAssignment(int $enrollmentId, int $classAssignmentId): JsonResponse
    {
        Enrollment::findOrFail($enrollmentId);

        $assignment = ClassAssignment::where('enrollment_id', $enrollmentId)->findOrFail($classAssignmentId);
        $assignment->delete();

        return $this->noContent();
    }

    public function documents(int $enrollmentId): JsonResponse
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $documents = $enrollment->documents()->orderBy('document_type')->get();

        $allDocuments = collect();

        foreach (DocumentType::cases() as $type) {
            $existing = $documents->first(fn ($d) => $d->document_type === $type);
            if ($existing) {
                $allDocuments->push($existing);
                continue;
            }

            $doc = new EnrollmentDocument;
            $doc->enrollment_id = $enrollmentId;
            $doc->document_type = $type;
            $doc->status = DocumentStatus::NotUploaded;
            $allDocuments->push($doc);
        }

        return $this->success(EnrollmentDocumentResource::collection($allDocuments));
    }

    public function uploadDocument(UploadDocumentRequest $request, int $enrollmentId): JsonResponse
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $documentType = $request->validated('document_type');

        $existing = $enrollment->documents()
            ->where('document_type', $documentType)
            ->first();

        if ($existing && ! $existing->canUpload()) {
            return $this->error('Este documento não pode receber upload no status atual', 422);
        }

        if ($existing?->hasFile()) {
            Storage::disk('local')->delete($existing->file_path);
        }

        $file = $request->file('file');
        $path = $file->store("enrollment-documents/{$enrollmentId}", 'local');

        $document = EnrollmentDocument::updateOrCreate(
            [
                'enrollment_id' => $enrollmentId,
                'document_type' => $documentType,
            ],
            [
                'status' => DocumentStatus::PendingReview,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'notes' => $request->validated('notes'),
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
            ],
        );

        return $this->success(new EnrollmentDocumentResource($document));
    }

    public function downloadDocument(int $enrollmentId, string $documentType): StreamedResponse
    {
        Enrollment::findOrFail($enrollmentId);

        $document = EnrollmentDocument::where('enrollment_id', $enrollmentId)
            ->where('document_type', $documentType)
            ->firstOrFail();

        if (! $document->hasFile()) {
            abort(404, 'Arquivo não encontrado');
        }

        return Storage::disk('local')->download($document->file_path, $document->original_filename);
    }

    public function reviewDocument(ReviewDocumentRequest $request, int $enrollmentId, string $documentType): JsonResponse
    {
        Enrollment::findOrFail($enrollmentId);

        $document = EnrollmentDocument::where('enrollment_id', $enrollmentId)
            ->where('document_type', $documentType)
            ->firstOrFail();

        if (! $document->canReview()) {
            return $this->error('Este documento não está aguardando revisão', 422);
        }

        $action = $request->validated('action');

        $updateData = [
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ];

        if ($action === 'approve') {
            $updateData['status'] = DocumentStatus::Approved;
        }

        if ($action === 'reject') {
            $updateData['status'] = DocumentStatus::Rejected;
            $updateData['rejection_reason'] = $request->validated('rejection_reason');
        }

        $document->update($updateData);

        return $this->success(new EnrollmentDocumentResource($document->fresh()));
    }
}
