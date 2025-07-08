window.taskApp = function () {
    function getHeaders() {
        return {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        };
    }

    return {
        tasks: [],
        currentPage: 1,
        lastPage: 1,
        totalCount: 0,
        completedCount: 0,
        pendingCount: 0,
        open: false,
        newTask: { title: "", description: "", due_date: "" },
        editTask: null,
        errors: {}, 
        updateerrors: {}, 

        init() {
            this.fetchTasks(1);
            this.fetchStats();
        },

        fetchTasks(page = 1) {
            fetch(`/tasks?page=${page}`, {
                headers: { Accept: "application/json" },
            }).then((res) => res.json())
                .then((data) => {
                    this.tasks = data.data;
                    this.currentPage = data.current_page;
                    this.lastPage = data.last_page;
                    window.history.pushState({}, "", `?page=${page}`);
                }).catch((err) => console.error("Fetch tasks error:", err));
        },

        fetchStats() {
            fetch("/tasks/stats", {
                headers: { Accept: "application/json" },
            }).then((res) => res.json())
                .then((data) => {
                    this.totalCount = data.total;
                    this.completedCount = data.completed;
                    this.pendingCount = data.pending;
                }).catch((err) => console.error("Fetch stats error:", err));
        },

        addTask() {
            fetch("/tasks", {
                method: "POST",
                headers: getHeaders(),
                body: JSON.stringify(this.newTask),
            }).then((res) => {
                    if (res.status === 422)
                        return res.json().then((err) => {
                            throw err;
                        });
                    if (!res.ok) throw new Error("Failed to add");
                    return res.json();
                }).then(() => {
                    Toastify({
                        text: "Task Added Successfully",
                        duration: 4000,
                        gravity: "top",
                        position: "center",
                        style: { background: "#16a34a" },
                    }).showToast();

                    this.newTask = { title: "", description: "", due_date: "" };
                    this.errors = {};
                    this.fetchTasks(this.currentPage);
                    this.fetchStats();
                }).catch((err) => {
                    this.errors = err.errors || {};
                });
        },

        deleteTask(id) {
            if (!id || typeof id !== "number") {
                console.error("Invalid ID for deletion:", id);
                return;
            }

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/tasks/${id}`, {
                        method: "DELETE",
                        headers: getHeaders(),
                    }).then((response) => {
                            if (!response.ok)
                                throw new Error("Failed to delete");

                            Toastify({
                                text: "Task Deleted Successfully",
                                duration: 4000,
                                gravity: "top",
                                position: "center",
                                style: { background: "#dc2626" },
                            }).showToast();

                            if (
                                this.tasks.length === 1 &&
                                this.currentPage > 1
                            ) {
                                this.fetchTasks(this.currentPage - 1);
                            } else {
                                this.fetchTasks(this.currentPage);
                            }
                            this.fetchStats();
                        }).catch((err) =>
                            console.error("Delete task error:", err)
                        );
                }
            });
        },

        toggleStatus(id) {
            fetch(`/tasks/${id}/toggle`, {
                method: "PATCH",
                headers: getHeaders(),
            }).then((res) => {
                    if (!res.ok) throw new Error("Failed to toggle status");
                    return res.json();
                }).then((updated) => {
                    const i = this.tasks.findIndex((t) => t.id === id);
                    if (i !== -1)
                        this.tasks[i].is_completed = updated.is_completed;
                    this.fetchStats();
                }).catch((err) =>
                    console.error("Toggle task status error:", err)
                );
        },

        editModal(task) {
            this.editTask = { ...task };
            this.open = true;
            this.updateerrors = {}; 
        },

        updateTask() {
            fetch(`/tasks/${this.editTask.id}`, {
                method: "PUT",
                headers: getHeaders(),
                body: JSON.stringify(this.editTask),
            }).then((res) => {
                    if (res.status === 422)
                        return res.json().then((err) => {
                            throw err;
                        });
                    if (!res.ok) throw new Error("Failed to update");
                    return res.json();
                }).then((updated) => {
                    const i = this.tasks.findIndex((t) => t.id === updated.id);
                    if (i !== -1) this.tasks[i] = updated;
                    this.open = false;
                    this.updateerrors = {};
                    Toastify({
                        text: "Task Updated Successfully",
                        duration: 4000,
                        gravity: "top",
                        position: "center",
                        style: { background: "#0284c7" },
                    }).showToast();
                    this.fetchStats();
                }).catch((err) => {
                    this.updateerrors = err.errors || {};
                });
        },

        formatDate(dateStr) {
            const options = {
                timeZone: "Asia/Manila",
                year: "numeric",
                month: "long",
                day: "numeric",
            };
            return new Date(dateStr).toLocaleDateString("en-PH", options);
        },
    };
};
