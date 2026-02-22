<?php

namespace App\Modules\Curriculum\Presentation\Controllers;

use App\Modules\Curriculum\Domain\Entities\TimeSlot;
use App\Modules\Curriculum\Presentation\Requests\CreateTimeSlotRequest;
use App\Modules\Curriculum\Presentation\Requests\UpdateTimeSlotRequest;
use App\Modules\Curriculum\Presentation\Resources\TimeSlotResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeSlotController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $slots = TimeSlot::with('shift')
            ->when($request->query('shift_id'), fn ($q, $id) => $q->where('shift_id', $id))
            ->orderBy('shift_id')
            ->orderBy('number')
            ->get();

        return $this->success(TimeSlotResource::collection($slots));
    }

    public function store(CreateTimeSlotRequest $request): JsonResponse
    {
        $slot = TimeSlot::create($request->validated());

        return $this->created(new TimeSlotResource($slot->load('shift')));
    }

    public function show(int $id): JsonResponse
    {
        $slot = TimeSlot::with('shift')->findOrFail($id);

        return $this->success(new TimeSlotResource($slot));
    }

    public function update(UpdateTimeSlotRequest $request, int $id): JsonResponse
    {
        $slot = TimeSlot::findOrFail($id);
        $slot->update($request->validated());

        return $this->success(new TimeSlotResource($slot->refresh()->load('shift')));
    }

    public function destroy(int $id): JsonResponse
    {
        TimeSlot::findOrFail($id)->delete();

        return $this->noContent();
    }
}
