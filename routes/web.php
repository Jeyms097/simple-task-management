<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;


Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
Route::resource('tasks', TaskController::class)->except(['show']);
Route::patch('tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
Route::get('/tasks/stats', function () {
    return response()->json([
        'total' => \App\Models\Task::count(),
        'completed' => \App\Models\Task::where('is_completed', true)->count(),
        'pending' => \App\Models\Task::where('is_completed', false)->count(),
    ]);
});
