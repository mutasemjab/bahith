<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\WeeklyPlanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeeklyPlannerController extends Controller
{
    use ApiResponse;

    // GET /weekly-planner  [auth]
    // Returns the latest planner (for the student's class) whose start_date has arrived.
    public function index(Request $request): JsonResponse
    {
        $student = $request->user();

        $query = WeeklyPlanner::active()->reached();

        if ($student->class_id) {
            $query->where('class_id', $student->class_id);
        }

        $planner = $query->latest('start_date')->first();

        if (! $planner) {
            return $this->success(null, 'No active planner for this week.');
        }

        return $this->success([
            'id'         => $planner->id,
            'title'      => $planner->title,
            'image'      => asset('assets/uploads/weekly-planners/' . $planner->image),
            'start_date' => $planner->start_date->toDateString(),
            'end_date'   => $planner->end_date->toDateString(),
        ]);
    }
}
