<!DOCTYPE html>
<html lang="en" x-data="{ open: false, task: null }" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="p-10 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Task Manager</h1>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">{{ session('success') }}</div>
    @endif

    {{-- Add Task Form --}}
    <form method="POST" action="{{ route('tasks.store') }}" class="mb-6 space-y-2">
        @csrf
        <input type="text" name="title" placeholder="Title" class="w-full border p-2" value="{{ old('title') }}">
        @error('title') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

        <textarea name="description" placeholder="Description" class="w-full border p-2">{{ old('description') }}</textarea>
        <input type="date" name="due_date" class="border p-2" value="{{ old('due_date') }}">

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Add Task</button>
    </form>

    {{-- Task List --}}
    <ul>
        @foreach($tasks as $task)
            <li class="mb-4 border-b pb-2">
                <div class="flex justify-between items-center">
                    <div>
                        <strong class="{{ $task->is_completed ? 'line-through text-gray-500' : '' }}">
                            {{ $task->title }}
                        </strong>
                        <p class="text-sm text-gray-600">{{ $task->description }}</p>
                        <p class="text-xs text-gray-400">Due: {{ $task->due_date ?? 'None' }}</p>
                    </div>
                    <div class="flex space-x-2">
                        {{-- Toggle --}}
                        <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="text-sm text-blue-500">
                                {{ $task->is_completed ? 'Mark as Pending' : 'Mark as Done' }}
                            </button>
                        </form>

                        {{-- Edit --}}
                        <button 
                            type="button"
                            class="text-yellow-500 text-sm"
                            @click="open = true; task = {{ Js::from($task) }}"
                        >
                            Edit
                        </button>

                        {{-- Delete --}}
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-red-500 text-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    {{-- Pagination --}}
    <div class="mt-4">{{ $tasks->links() }}</div>

    {{-- Edit Modal --}}
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-md relative" @click.outside="open = false">
            <button @click="open = false" class="absolute top-2 right-2 text-gray-500 hover:text-black">&times;</button>
            <h2 class="text-lg font-semibold mb-4">Edit Task</h2>

            <template x-if="task">
                <form method="POST" :action="`/tasks/${task.id}`">
                    @csrf
                    @method('PUT')
                    <input type="text" name="title" x-model="task.title" class="w-full border p-2 mb-2" placeholder="Title" required>
                    <textarea name="description" x-model="task.description" class="w-full border p-2 mb-2" placeholder="Description"></textarea>
                    <input type="date" name="due_date" x-model="task.due_date" class="w-full border p-2 mb-4">

                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update</button>
                </form>
            </template>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
