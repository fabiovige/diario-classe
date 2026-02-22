<?php

namespace App\Modules\Assessment\Presentation\Controllers;

use App\Modules\Assessment\Application\DTOs\CreateAssessmentConfigDTO;
use App\Modules\Assessment\Application\DTOs\RecordBulkGradesDTO;
use App\Modules\Assessment\Application\UseCases\CalculatePeriodAverageUseCase;
use App\Modules\Assessment\Application\UseCases\CreateAssessmentConfigUseCase;
use App\Modules\Assessment\Application\UseCases\RecordBulkGradesUseCase;
use App\Modules\Assessment\Application\UseCases\RecordDescriptiveReportUseCase;
use App\Modules\Assessment\Application\UseCases\RecordRecoveryGradeUseCase;
use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\DescriptiveReport;
use App\Modules\Assessment\Domain\Entities\FinalAverage;
use App\Modules\Assessment\Domain\Entities\Grade;
use App\Modules\Assessment\Domain\Entities\PeriodAverage;
use App\Modules\Assessment\Presentation\Requests\CreateAssessmentConfigRequest;
use App\Modules\Assessment\Presentation\Requests\RecordBulkGradesRequest;
use App\Modules\Assessment\Presentation\Requests\RecordDescriptiveReportRequest;
use App\Modules\Assessment\Presentation\Requests\RecordRecoveryGradeRequest;
use App\Modules\Assessment\Presentation\Resources\AssessmentConfigResource;
use App\Modules\Assessment\Presentation\Resources\DescriptiveReportResource;
use App\Modules\Assessment\Presentation\Resources\GradeResource;
use App\Modules\Assessment\Presentation\Resources\PeriodAverageResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentController extends ApiController
{
    public function bulkGrades(RecordBulkGradesRequest $request, RecordBulkGradesUseCase $useCase): JsonResponse
    {
        $grades = $useCase->execute(new RecordBulkGradesDTO(
            classGroupId: $request->validated('class_group_id'),
            teacherAssignmentId: $request->validated('teacher_assignment_id'),
            assessmentPeriodId: $request->validated('assessment_period_id'),
            assessmentInstrumentId: $request->validated('assessment_instrument_id'),
            grades: $request->validated('grades'),
        ));

        return $this->created(GradeResource::collection($grades));
    }

    public function indexGrades(Request $request): JsonResponse
    {
        $grades = Grade::with('student')
            ->when($request->query('student_id'), fn ($q, $id) => $q->where('student_id', $id))
            ->when($request->query('class_group_id'), fn ($q, $id) => $q->where('class_group_id', $id))
            ->when($request->query('teacher_assignment_id'), fn ($q, $id) => $q->where('teacher_assignment_id', $id))
            ->when($request->query('assessment_period_id'), fn ($q, $id) => $q->where('assessment_period_id', $id))
            ->when($request->query('assessment_instrument_id'), fn ($q, $id) => $q->where('assessment_instrument_id', $id))
            ->orderBy('student_id')
            ->paginate($request->query('per_page', 50));

        return $this->success(GradeResource::collection($grades)->response()->getData(true));
    }

    public function updateGrade(Request $request, int $id): JsonResponse
    {
        $grade = Grade::findOrFail($id);
        $grade->update($request->only(['numeric_value', 'conceptual_value', 'observations']));

        return $this->success(new GradeResource($grade->refresh()->load('student')));
    }

    public function recoveryGrade(RecordRecoveryGradeRequest $request, RecordRecoveryGradeUseCase $useCase): JsonResponse
    {
        $grade = $useCase->execute(
            studentId: $request->validated('student_id'),
            classGroupId: $request->validated('class_group_id'),
            teacherAssignmentId: $request->validated('teacher_assignment_id'),
            assessmentPeriodId: $request->validated('assessment_period_id'),
            assessmentInstrumentId: $request->validated('assessment_instrument_id'),
            numericValue: $request->validated('numeric_value'),
            conceptualValue: $request->validated('conceptual_value'),
            recoveryType: $request->validated('recovery_type'),
        );

        return $this->created(new GradeResource($grade->load('student')));
    }

    public function calculatePeriodAverage(Request $request, CalculatePeriodAverageUseCase $useCase): JsonResponse
    {
        $average = $useCase->execute(
            studentId: (int) $request->input('student_id'),
            classGroupId: (int) $request->input('class_group_id'),
            teacherAssignmentId: (int) $request->input('teacher_assignment_id'),
            assessmentPeriodId: (int) $request->input('assessment_period_id'),
        );

        return $this->success(new PeriodAverageResource($average));
    }

    public function storeDescriptiveReport(RecordDescriptiveReportRequest $request, RecordDescriptiveReportUseCase $useCase): JsonResponse
    {
        $report = $useCase->execute(
            studentId: $request->validated('student_id'),
            classGroupId: $request->validated('class_group_id'),
            experienceFieldId: $request->validated('experience_field_id'),
            assessmentPeriodId: $request->validated('assessment_period_id'),
            content: $request->validated('content'),
        );

        return $this->created(new DescriptiveReportResource($report));
    }

    public function showDescriptiveReport(int $id): JsonResponse
    {
        $report = DescriptiveReport::with(['student', 'classGroup.gradeLevel', 'classGroup.shift', 'experienceField', 'assessmentPeriod'])
            ->findOrFail($id);

        return $this->success(new DescriptiveReportResource($report));
    }

    public function updateDescriptiveReport(Request $request, int $id): JsonResponse
    {
        $report = DescriptiveReport::findOrFail($id);
        $report->update($request->only(['student_id', 'class_group_id', 'experience_field_id', 'assessment_period_id', 'content']));

        return $this->success(new DescriptiveReportResource(
            $report->refresh()->load(['student', 'classGroup.gradeLevel', 'classGroup.shift', 'experienceField', 'assessmentPeriod'])
        ));
    }

    public function indexDescriptiveReports(Request $request): JsonResponse
    {
        $reports = DescriptiveReport::with(['student', 'classGroup.gradeLevel', 'classGroup.shift', 'experienceField', 'assessmentPeriod'])
            ->when($request->query('student_id'), fn ($q, $id) => $q->where('student_id', $id))
            ->when($request->query('class_group_id'), fn ($q, $id) => $q->where('class_group_id', $id))
            ->when($request->query('assessment_period_id'), fn ($q, $id) => $q->where('assessment_period_id', $id))
            ->paginate($request->query('per_page', 15));

        return $this->success(DescriptiveReportResource::collection($reports)->response()->getData(true));
    }

    public function reportCard(int $studentId): JsonResponse
    {
        $periodAverages = PeriodAverage::where('student_id', $studentId)
            ->with(['assessmentPeriod', 'teacherAssignment.curricularComponent'])
            ->orderBy('assessment_period_id')
            ->get();

        $finalAverages = FinalAverage::where('student_id', $studentId)
            ->with(['teacherAssignment.curricularComponent'])
            ->get();

        $descriptiveReports = DescriptiveReport::where('student_id', $studentId)
            ->with(['experienceField', 'assessmentPeriod'])
            ->get();

        return $this->success([
            'period_averages' => PeriodAverageResource::collection($periodAverages),
            'final_averages' => $finalAverages,
            'descriptive_reports' => DescriptiveReportResource::collection($descriptiveReports),
        ]);
    }

    public function indexConfigs(Request $request): JsonResponse
    {
        $configs = AssessmentConfig::with(['conceptualScales', 'instruments'])
            ->when($request->query('school_id'), fn ($q, $id) => $q->where('school_id', $id))
            ->when($request->query('academic_year_id'), fn ($q, $id) => $q->where('academic_year_id', $id))
            ->when($request->query('grade_level_id'), fn ($q, $id) => $q->where('grade_level_id', $id))
            ->paginate($request->query('per_page', 15));

        return $this->success(AssessmentConfigResource::collection($configs)->response()->getData(true));
    }

    public function storeConfig(CreateAssessmentConfigRequest $request, CreateAssessmentConfigUseCase $useCase): JsonResponse
    {
        $config = $useCase->execute(new CreateAssessmentConfigDTO(
            schoolId: $request->validated('school_id'),
            academicYearId: $request->validated('academic_year_id'),
            gradeLevelId: $request->validated('grade_level_id'),
            gradeType: $request->validated('grade_type'),
            scaleMin: $request->validated('scale_min'),
            scaleMax: $request->validated('scale_max'),
            passingGrade: $request->validated('passing_grade'),
            averageFormula: $request->validated('average_formula', 'arithmetic'),
            roundingPrecision: $request->validated('rounding_precision', 1),
            recoveryEnabled: $request->validated('recovery_enabled', true),
            recoveryReplaces: $request->validated('recovery_replaces', 'higher'),
        ));

        return $this->created(new AssessmentConfigResource($config->load(['conceptualScales', 'instruments'])));
    }
}
