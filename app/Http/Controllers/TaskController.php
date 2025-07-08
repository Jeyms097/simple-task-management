<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::orderBy('created_at', 'desc')->paginate(4);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $tasks->items(),
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'total' => Task::count(),
                'completed' => Task::where('is_completed', true)->count(),
                'pending' => Task::where('is_completed', false)->count(),
            ]);
        }

        return view('main', compact('tasks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        $validated['is_completed'] = false;
        $task = Task::create($validated);
        return response()->json($task);
    }

    public function edit(Task $task)
    {
        return view('edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        $task->update($validated);
        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task Deleted']);
    }


    public function toggle(Task $task)
    {
        $task->is_completed = !$task->is_completed;
        $task->save();

        return response()->json($task);
    }
}
