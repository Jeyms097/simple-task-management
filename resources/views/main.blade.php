<!DOCTYPE html>
<html lang="en" x-data="taskApp()" x-init="init()" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/main.js'])
</head>
<body class="p-10 max-w-2xl mx-auto bg-gray-100 text-gray-900">

    <h1 class="text-3xl font-bold mb-6">📝 Task Manager</h1>

    {{-- Success Message --}}
    <div 
        x-show="successMessage"
        x-transition
        class="bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded mb-4"
    >
        <span x-text="successMessage"></span>
    </div>

    {{-- Add Task Form --}}
    <form @submit.prevent="addTask" class="mb-6 bg-white p-4 rounded shadow space-y-3">
        <input type="text" x-model="newTask.title" placeholder="Title" class="w-full border p-2 rounded">
        <textarea x-model="newTask.description" placeholder="Description" class="w-full border p-2 rounded"></textarea>
        <input type="date" x-model="newTask.due_date" class="w-full border p-2 rounded">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full cursor-pointer">Add Task</button>
    </form>

    {{-- Task List --}}
    <ul class="space-y-4">
        <template x-for="task in tasks" :key="task.id">
            <li class="bg-white p-4 rounded shadow flex justify-between items-start">
                <div>
                    <strong class="text-lg" :class="{ 'line-through text-gray-400': task.is_completed }" x-text="task.title"></strong>
                    <p class="text-sm text-gray-600" x-text="task.description"></p>
                    <p class="text-xs text-gray-500">Due: <span x-text="task.due_date ?? 'None'"></span></p>
                    <p class="text-xs text-gray-400 italic" x-text="'Created: ' + formatDate(task.created_at)"></p>
                </div>
                <div class="flex flex-col items-end space-y-1 text-sm">
                    <button @click="toggleStatus(task.id)" class="text-blue-500 hover:underline cursor-pointer" x-text="task.is_completed ? 'Pending' : 'Completed'"></button>
                    <button @click="editModal(task)" class="text-yellow-500 hover:underline cursor-pointer">Edit</button>
                    <button @click="deleteTask(task.id)" class="text-red-500 hover:underline cursor-pointer">Delete</button>
                </div>
            </li>
        </template>
    </ul>

    {{-- Async Pagination --}}
    <div class="mt-6 flex justify-between items-center text-sm">
        <button 
            @click="fetchTasks(currentPage - 1)" 
            :disabled="currentPage <= 1"
            class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 cursor-pointer"
        >
            Previous
        </button>
        <span>Page <span x-text="currentPage"></span> of <span x-text="lastPage"></span></span>
        <button 
            @click="fetchTasks(currentPage + 1)" 
            :disabled="currentPage >= lastPage"
            class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 cursor-pointer"
        >
            Next
        </button>
    </div>

    {{-- Edit Modal --}}
    <div x-show="open" x-transition class="fixed inset-0 z-50 bg-black/30 backdrop-blur-sm flex items-center justify-center" style="display: none;">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md relative">
            <button @click="open = false" class="absolute top-3 right-4 text-gray-500 hover:text-black text-2xl leading-none">&times;</button>
            <h2 class="text-xl font-semibold mb-4">Edit Task</h2>

            <template x-if="editTask">
                <form @submit.prevent="updateTask" class="space-y-3">
                    <input type="text" x-model="editTask.title" class="w-full border p-2 rounded" placeholder="Title" required>
                    <textarea x-model="editTask.description" class="w-full border p-2 rounded" placeholder="Description"></textarea>
                    <input type="date" x-model="editTask.due_date" class="w-full border p-2 rounded">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded w-full cursor-pointer">Update</button>
                </form>
            </template>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
