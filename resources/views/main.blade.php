<!DOCTYPE html>
<html lang="en" x-data="taskApp()" x-init="init()" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/main.js'])
    <link href="https://fonts.bunny.net/css?family=Poppins:400,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body class="p-10 max-w-6xl mx-auto bg-gray-100 text-gray-900">

    <div class="md:flex md:gap-6 mb-10">
        {{-- title --}}
        <div class="md:w-1/2 bg-white rounded shadow p-6 mb-6 md:mb-0 flex flex-col items-center justify-center text-center">
            <h1 class="text-3xl font-bold mb-6 uppercase">Task Manager</h1>
            <div class="flex flex-wrap justify-center gap-3 text-sm">
                <span class="bg-gray-200 px-3 py-2 rounded" x-text="'Total: ' + totalCount"></span>
                <span class="bg-green-200 text-green-800 px-3 py-2 rounded" x-text="'Completed: ' + completedCount"></span>
                <span class="bg-yellow-200 text-yellow-800 px-3 py-2 rounded" x-text="'Pending: ' + pendingCount"></span>
            </div>
        </div>

        {{-- form for adding tasks --}}
        <div class="md:w-1/2 bg-white rounded shadow p-6">
            <form @submit.prevent="addTask" class="space-y-3">
                @csrf
                <input type="text" x-model="newTask.title" placeholder="Title" class="w-full border p-2 rounded">
                <template x-if="errors.title">
                    <p class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded text-sm" x-text="errors.title[0]"></p>
                </template>
                <textarea x-model="newTask.description" placeholder="Description" class="w-full border p-2 rounded"></textarea>
                <input type="date" x-model="newTask.due_date" class="w-full border p-2 rounded">
                <template x-if="errors.due_date">
                    <p class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded text-sm" x-text="errors.due_date[0]"></p>
                </template>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 text-sm rounded cursor-pointer ease-in duration-300">Add Task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- task list --}}
    <template x-if="tasks.length > 0">
        <ul class="space-y-4">
            <template x-for="task in tasks" :key="task.id">
                <li class="relative group bg-white rounded-xl shadow hover:shadow-md transition duration-300 border-l-4"
                    :class="task.is_completed ? 'border-green-500' : 'border-yellow-400'">
                    <div class="p-4 space-y-4">

                        {{-- dropdown --}}
                        <div class="absolute top-2 right-3" x-data="{ openMenu: false }">
                            <button @click="openMenu = !openMenu" class="text-gray-500 hover:text-gray-800 text-2xl font-bold cursor-pointer">⋯</button>
                            <div x-show="openMenu" @click.outside="openMenu = false" x-transition
                                class="absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded shadow-md z-10 text-sm">
                                <button @click="toggleStatus(task.id); openMenu = false"
                                    class="w-full text-left px-4 py-2 hover:bg-gray-100 text-green-800 cursor-pointer"
                                    x-text="task.is_completed ? 'Undo' : 'Complete'"></button>
                                <button @click="editModal(task); openMenu = false"
                                    class="w-full text-left px-4 py-2 hover:bg-gray-100 text-blue-700 cursor-pointer">Edit</button>
                                <button @click="deleteTask(task.id); openMenu = false"
                                    class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600 cursor-pointer">Delete</button>
                            </div>
                        </div>

                        {{-- created date and status --}}
                        <div>
                            <p class="text-xs italic"
                               :class="task.is_completed ? 'text-gray-400' : 'text-gray-900'"
                               x-text="'Created: ' + formatDate(task.created_at)">
                            </p>
                            <p class="text-xs font-semibold inline-block rounded-full px-2 py-0.5 mt-1"
                               :class="task.is_completed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                               x-text="task.is_completed ? 'Completed' : 'Pending'">
                            </p>
                        </div>

                        {{-- title--}}
                        <div class="text-center">
                            <strong class="text-lg font-bold uppercase tracking-wide"
                                :class="task.is_completed ? 'text-gray-400 line-through' : 'text-gray-900'"
                                x-text="task.title">
                            </strong>
                        </div>

                        {{-- description --}}
                        <div>
                            <template x-if="task.description">
                                <div>
                                    <p class="text-xs leading-relaxed text-justify max-w-[90%] mx-auto italic"
                                    :class="task.is_completed ? 'text-gray-400' : 'text-gray-900'">
                                        Task Description:
                                    </p>
                                    <p class="text-sm leading-relaxed text-justify max-w-[90%] mx-auto"
                                    :class="task.is_completed ? 'text-gray-400' : 'text-gray-900'"
                                    x-text="task.description">
                                    </p>
                                </div>
                            </template>
                            <template x-if="!task.description">
                                <p class="text-sm text-gray-400 text-center italic">No description</p>
                            </template>
                        </div>

                        {{-- due date --}}
                        <div class="text-right text-xs"
                             :class="task.is_completed ? 'text-gray-400' : 'text-gray-900'">
                            Due Date:
                            <span x-text="task.due_date ? formatDate(task.due_date) : 'None'"></span>
                        </div>
                    </div>
                </li>
            </template>
        </ul>
    </template>

    {{-- message if no task --}}
    <template x-if="tasks.length === 0">
        <div class="bg-white p-6 rounded shadow text-center text-gray-500 font-medium italic">
            No task created
        </div>
    </template>

    {{-- pagination --}}
    <template x-if="tasks.length > 0">
        <div class="mt-6 flex justify-between items-center text-sm">
            <button @click="fetchTasks(currentPage - 1)" :disabled="currentPage <= 1"
                class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 cursor-pointer">Previous</button>
            <span>Page <span x-text="currentPage"></span> of <span x-text="lastPage"></span></span>
            <button @click="fetchTasks(currentPage + 1)" :disabled="currentPage >= lastPage"
                class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 cursor-pointer">Next</button>
        </div>
    </template>

    {{-- modal for edit --}}
    <div x-show="open" x-transition class="fixed inset-0 z-50 bg-black/30 backdrop-blur-sm flex items-center justify-center" style="display: none;">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md relative">
            <button @click="open = false" class="absolute top-3 right-4 text-gray-500 hover:text-black text-2xl leading-none cursor-pointer">&times;</button>
            <h2 class="text-xl font-semibold mb-4">Update Task</h2>
            <template x-if="editTask">
                <form @submit.prevent="updateTask" class="space-y-3">
                    @csrf
                    <input type="text" x-model="editTask.title" class="w-full border p-2 rounded" placeholder="Title">
                    <template x-if="updateerrors.title">
                        <p class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded text-sm" x-text="updateerrors.title[0]"></p>
                    </template>

                    <textarea x-model="editTask.description" class="w-full border p-2 rounded" placeholder="Description"></textarea>

                    <input type="date" x-model="editTask.due_date" class="w-full border p-2 rounded">
                    <template x-if="updateerrors.due_date">
                        <p class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded text-sm" x-text="updateerrors.due_date[0]"></p>
                    </template>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 text-sm rounded cursor-pointer ease-in duration-300">Update</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
