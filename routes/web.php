<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

// Route::get('/', function () { return view('main');});

Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
Route::resource('tasks', TaskController::class)->except(['show']);
Route::patch('tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');


