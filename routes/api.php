<?php

use App\Http\Controllers\Api\Student\AuthController;
use App\Http\Controllers\Api\Student\CategoryController;
use App\Http\Controllers\Api\Student\CourseController;
use App\Http\Controllers\Api\Student\EducationalNoteController;
use App\Http\Controllers\Api\Student\ExamController;
use App\Http\Controllers\Api\Student\HomeController;
use App\Http\Controllers\Api\Student\PreviousYearExamController;
use App\Http\Controllers\Api\Student\ProfileController;
use App\Http\Controllers\Api\Student\QuestionBankController;
use App\Http\Controllers\Api\Student\TeacherController;
use App\Http\Controllers\Api\Student\WorksheetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Mobile API — v1
|--------------------------------------------------------------------------
| Base URL : /api/v1/student/...
| Auth     : Laravel Sanctum — Bearer token
| Locale   : Accept-Language: ar|en  (default: ar)
|
| Response format:
|   { "status": true|false, "message": "...", "data": {...} }
|   Paginated: adds "pagination": { current_page, last_page, per_page, total }
|--------------------------------------------------------------------------
*/

Route::prefix('v1/student')->middleware('api.locale')->group(function () {

    // ── Auth (public) ──────────────────────────────────────────────────────
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login',    [AuthController::class, 'login']);

    // ── Home ───────────────────────────────────────────────────────────────
    Route::get('home', [HomeController::class, 'index']);

    // ── Category tree navigation ───────────────────────────────────────────
    // GET /categories                      → root categories
    // GET /categories/{id}                 → category + its children + subjects
    // GET /subjects/{id}                   → subject + its courses
    Route::get('categories',        [CategoryController::class, 'index']);
    Route::get('categories/{id}',   [CategoryController::class, 'show']);
    Route::get('subjects/{id}',     [CategoryController::class, 'subject']);

    // ── Courses ────────────────────────────────────────────────────────────
    // Filters: category_id, subject_id, teacher_id, search, featured, trending
    Route::get('courses',       [CourseController::class, 'index']);
    Route::get('courses/{id}',  [CourseController::class, 'show']);

    // ── Teachers ───────────────────────────────────────────────────────────
    Route::get('teachers',      [TeacherController::class, 'index']);
    Route::get('teachers/{id}', [TeacherController::class, 'show']);

    // ── Exams (public list + detail) ───────────────────────────────────────
    // Filters: course_id, subject_id, exam_type, search
    Route::get('exams',      [ExamController::class, 'index']);
    Route::get('exams/{id}', [ExamController::class, 'show']);

    // ── Previous Year Exams ────────────────────────────────────────────────
    // Filters: subject_id, year, search
    Route::get('previous-year-exams',       [PreviousYearExamController::class, 'index']);
    Route::get('previous-year-exams/{id}',  [PreviousYearExamController::class, 'show']);

    // ── Question Bank ──────────────────────────────────────────────────────
    // Filters: subject_id, search
    Route::get('question-banks',      [QuestionBankController::class, 'index']);
    Route::get('question-banks/{id}', [QuestionBankController::class, 'show']);

    // ── Worksheets ─────────────────────────────────────────────────────────
    // Filters: subject_id, year, search
    Route::get('worksheets',      [WorksheetController::class, 'index']);
    Route::get('worksheets/{id}', [WorksheetController::class, 'show']);

    // ── Protected routes (require Bearer token) ────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Profile
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);

        // My courses (enrolled)
        Route::get('my-courses', [ProfileController::class, 'myCourses']);

        // My exam history
        Route::get('my-exams', [ProfileController::class, 'myExams']);

        // Exam flow
        Route::post('exams/{id}/start',           [ExamController::class, 'start']);
        Route::post('attempts/{attempt}/submit',  [ExamController::class, 'submit']);

        // Educational notes (المفكرة التعليمية — filtered by student's class)
        Route::get('educational-notes', [EducationalNoteController::class, 'index']);
    });
});
