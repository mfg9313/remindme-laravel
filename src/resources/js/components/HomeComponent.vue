<template>
    <div class="home-container dark:bg-gray-900">
        <header class="flex justify-between items-center p-4 bg-gray-800 text-white">
            <button @click="logout" class="ml-auto px-4 py-2 bg-red-700 rounded hover:bg-red-900">
                <!-- SVG Icon -->
                <svg viewBox="0 -0.5 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path d="M11.75 9.874C11.75 10.2882 12.0858 10.624 12.5 10.624C12.9142 10.624 13.25 10.2882 13.25 9.874H11.75ZM13.25 4C13.25 3.58579 12.9142 3.25 12.5 3.25C12.0858 3.25 11.75 3.58579 11.75 4H13.25ZM9.81082 6.66156C10.1878 6.48991 10.3542 6.04515 10.1826 5.66818C10.0109 5.29121 9.56615 5.12478 9.18918 5.29644L9.81082 6.66156ZM5.5 12.16L4.7499 12.1561L4.75005 12.1687L5.5 12.16ZM12.5 19L12.5086 18.25C12.5029 18.25 12.4971 18.25 12.4914 18.25L12.5 19ZM19.5 12.16L20.2501 12.1687L20.25 12.1561L19.5 12.16ZM15.8108 5.29644C15.4338 5.12478 14.9891 5.29121 14.8174 5.66818C14.6458 6.04515 14.8122 6.48991 15.1892 6.66156L15.8108 5.29644ZM13.25 9.874V4H11.75V9.874H13.25ZM9.18918 5.29644C6.49843 6.52171 4.7655 9.19951 4.75001 12.1561L6.24999 12.1639C6.26242 9.79237 7.65246 7.6444 9.81082 6.66156L9.18918 5.29644ZM4.75005 12.1687C4.79935 16.4046 8.27278 19.7986 12.5086 19.75L12.4914 18.25C9.08384 18.2892 6.28961 15.5588 6.24995 12.1513L4.75005 12.1687ZM12.4914 19.75C16.7272 19.7986 20.2007 16.4046 20.2499 12.1687L18.7501 12.1513C18.7104 15.5588 15.9162 18.2892 12.5086 18.25L12.4914 19.75ZM20.25 12.1561C20.2345 9.19951 18.5016 6.52171 15.8108 5.29644L15.1892 6.66156C17.3475 7.6444 18.7376 9.79237 18.75 12.1639L20.25 12.1561Z" fill="#ffffff"></path>
                    </g>
                </svg>
            </button>
        </header>

        <main class="p-10">
            <h1 class="pb-10 text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">Dashboard</h1>

            <!-- Loading Indicator -->
            <div v-if="loading" class="text-center">
                <p class="text-gray-700 dark:text-gray-400">Loading reminders...</p>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="text-red-500 text-center mb-4">
                {{ error }}
            </div>

            <!-- No Reminders Message -->
            <div v-if="!loading && reminders.length === 0"  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h5 class="mb-2 pb-5 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Welcome, {{ user.name }}!
                    </h5>
                    <p class="font-normal text-gray-700 dark:text-gray-400">
                        Please click the button in the bottom right corner to get started!
                    </p>
                </div>
            </div>

            <!-- Reminders List -->
            <div v-else-if="!loading && reminders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="reminder in reminders" :key="reminder.id" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white">{{ reminder.title }}</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ reminder.description }}</p>
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-300">Due: {{ formatDate(reminder.due_date) }}</p>
                </div>
            </div>
        </main>

        <!-- Adding Reminder Button -->
        <button @click="openModal" class="fixed bottom-6 right-6 bg-blue-600 hover:bg-blue-700 text-white rounded-full w-16 h-16 flex items-center justify-center shadow-lg focus:outline-none" aria-label="Add Reminder">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </button>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-11/12 max-w-md p-6">
                <h2 class="text-2xl mb-4 text-gray-800 dark:text-white">Add New Reminder</h2>
                <form @submit.prevent="submitForm">
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="title">Title</label>
                        <input type="text" id="title" v-model="form.title" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300 dark:bg-gray-700 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="description">Description</label>
                        <textarea id="description" v-model="form.description" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300 dark:bg-gray-700 dark:text-white" rows="4"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="due_date">Due Date</label>
                        <input type="date" id="due_date" v-model="form.due_date" :min="todayDate" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300 dark:bg-gray-700 dark:text-white" required>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" @click="closeModal" class="px-4 py-2 mr-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Add
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: "HomeComponent",
    data() {
        return {
            user: {},
            reminders: [],
            loading: false,
            error: '',
            // Modal visibility
            showModal: false,
            // Form data
            form: {
                title: '',
                description: '',
                due_date: ''
            }
        };
    },
    computed: {
        // Computes today's date in YYYY-MM-DD format for the min attribute of the due date input
        todayDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    },
    created() {
        // Retrieve user information from localStorage
        const userData = localStorage.getItem('user');
        if (userData) {
            this.user = JSON.parse(userData);
        } else {
            // If user data is missing, redirect to log in
            this.$router.push({ name: 'login' });
        }

        // Fetch reminders
        this.fetchReminders();
    },
    methods: {
        async fetchReminders() {
            this.loading = true;
            this.error = '';

            try {
                const response = await this.$api.get('/api/reminders');

                if (response.data.ok) {
                    this.reminders = response.data.data.reminders;
                } else {
                    this.error = response.data.msg || 'Failed to fetch reminders.';
                }
            } catch (error) {
                if (error.response) {
                    if (error.response.status === 401) {
                        // Token refresh failed or user is unauthorized
                        localStorage.removeItem('access_token');
                        localStorage.removeItem('refresh_token');
                        localStorage.removeItem('user');
                        this.$router.push({ name: 'login' });
                    } else {
                        this.error = error.response.data.msg || 'Failed to fetch reminders.';
                    }
                } else if (error.request) {
                    // Request was made but no response received
                    this.error = 'No response from the server. Please try again later.';
                } else {
                    // Something else caused the error
                    this.error = 'An unexpected error occurred.';
                }
                console.error('Fetch Reminders Error:', error);
            } finally {
                this.loading = false;
            }
        },
        async logout() {
            try {
                // Revoke the access token on the backend
                await this.$api.post('/api/logout');

                // Clear tokens and user data from localStorage
                localStorage.removeItem('access_token');
                localStorage.removeItem('refresh_token');
                localStorage.removeItem('user');

                // Redirect to the login page
                this.$router.push({ name: 'login' });
            } catch (error) {
                console.error('Logout Error:', error);
                // Even if the API call fails, clear local tokens and redirect
                localStorage.removeItem('access_token');
                localStorage.removeItem('refresh_token');
                localStorage.removeItem('user');
                this.$router.push({ name: 'login' });
            }
        },
        formatDate(dateStr) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateStr).toLocaleDateString(undefined, options);
        },
        // Modal control methods
        openModal() {
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.resetForm();
        },
        // Form submission
        async submitForm() {
            // Validate form inputs
            if (!this.form.title || !this.form.due_date) {
                this.error = 'Please fill in all fields.';
                return;
            }

            try {
                // Send form data to the backend API
                const response = await this.$api.post('/api/reminders', this.form);

                if (response.data.ok) {
                    // Successfully added the reminder
                    this.reminders.push(response.data.data.reminder);
                    this.closeModal();
                } else {
                    this.error = response.data.msg || 'Failed to add reminder.';
                }
            } catch (error) {
                if (error.response) {
                    this.error = error.response.data.msg || 'Failed to add reminder.';
                } else if (error.request) {
                    this.error = 'No response from the server. Please try again later.';
                } else {
                    this.error = 'An unexpected error occurred.';
                }
                console.error('Add Reminder Error:', error);
            }
        },
        // Reset form fields
        resetForm() {
            this.form.title = '';
            this.form.description = '';
            this.form.due_date = '';
            this.error = '';
        }
    }
}
</script>

<style scoped>
.home-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

header {
    background-color: #2d3748;

    button {
        cursor: pointer;
        height: 50px;
        width: 50px;
    }
}
</style>
