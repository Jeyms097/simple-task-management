window.taskApp = function () {
    function getHeaders() {
        return {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        };
    }

    return {
        tasks: [],
        currentPage: 1,
        lastPage: 1,
        open: false,
        newTask: { title: "", description: "", due_date: "" },
        editTask: null,
        successMessage: "",

        init() {
            this.fetchTasks(1);
        },

        fetchTasks(page = 1) {
            fetch(`/tasks?page=${page}`, {
                headers: {
                    "Accept": "application/json",
                }
            })
                .then(res => res.json())
                .then(data => {
                    this.tasks = data.data;
                    this.currentPage = data.current_page;
                    this.lastPage = data.last_page;
                    window.history.pushState({}, "", `?page=${page}`);
                })
                .catch(err => console.error("Fetch tasks error:", err));
        },

        addTask() {
            fetch("/tasks", {
                method: "POST",
                headers: getHeaders(),
                body: JSON.stringify(this.newTask),
            })
                .then(res => {
                    if (!res.ok) throw new Error("Failed to add");
                    return res.json();
                })
                .then((data) => {
                    this.successMessage = "Task added!";
                    this.newTask = { title: "", description: "", due_date: "" };
                    setTimeout(() => (this.successMessage = ""), 4000);
                    this.fetchTasks(this.currentPage);
                })
                .catch(err => console.error("Add task error:", err));
        },

        deleteTask(id) {
            if (!id || typeof id !== "number") {
                console.error("Invalid ID for deletion:", id);
                return;
            }

            fetch(`/tasks/${id}`, {
                method: "DELETE",
                headers: getHeaders(),
            })
                .then((response) => {
                    if (!response.ok) throw new Error("Failed to delete");
                    this.successMessage = "Task deleted!";
                    setTimeout(() => (this.successMessage = ""), 4000);

                    if (this.tasks.length === 1 && this.currentPage > 1) {
                        this.fetchTasks(this.currentPage - 1);
                    } else {
                        this.fetchTasks(this.currentPage);
                    }
                })
                .catch(err => console.error("Delete task error:", err));
        },

        toggleStatus(id) {
            fetch(`/tasks/${id}/toggle`, {
                method: "PATCH",
                headers: getHeaders(),
            })
                .then(res => {
                    if (!res.ok) throw new Error("Failed to toggle status");
                    return res.json();
                })
                .then(updated => {
                    const i = this.tasks.findIndex((t) => t.id === id);
                    if (i !== -1) this.tasks[i].is_completed = updated.is_completed;
                })
                .catch(err => console.error("Toggle task status error:", err));
        },

        editModal(task) {
            this.editTask = { ...task };
            this.open = true;
        },

        updateTask() {
            fetch(`/tasks/${this.editTask.id}`, {
                method: "PUT",
                headers: getHeaders(),
                body: JSON.stringify(this.editTask),
            })
                .then(res => {
                    if (!res.ok) throw new Error("Failed to update");
                    return res.json();
                })
                .then(updated => {
                    const i = this.tasks.findIndex((t) => t.id === updated.id);
                    if (i !== -1) this.tasks[i] = updated;
                    this.open = false;
                    this.successMessage = "Task updated!";
                    setTimeout(() => (this.successMessage = ""), 4000);
                })
                .catch(err => console.error("Update task error:", err));
        },

        formatDate(dateStr) {
            const options = {
                timeZone: "Asia/Manila",
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            };
            return new Date(dateStr).toLocaleString("en-PH", options);
        },
    };
};
