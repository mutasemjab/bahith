<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\WeeklyPlanner;
use Illuminate\Http\JsonResponse;

class WeeklyPlannerController extends Controller
{
    use ApiResponse;

    // GET /weekly-planner — returns the active planner for the current week
    public function index(): JsonResponse
    {
        $planner = WeeklyPlanner::active()
            ->current()
            ->latest('start_date')
            ->first();

        if (! $planner) {
            return $this->success(null, 'No active planner for this week.');
        }

        return $this->success([
            'id'         => $planner->id,
            'title'      => $planner->title,
            'image'      => asset($planner->image),
            'start_date' => $planner->start_date->toDateString(),
            'end_date'   => $planner->end_date->toDateString(),
        ]);
    }
}
