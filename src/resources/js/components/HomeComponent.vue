<template>
    <div>
        <div class="home-container dark:bg-gray-900">
            <header class="flex justify-between items-center p-4 bg-gray-800 text-white">
                <button @click="logout" class="ml-auto px-4 py-2 bg-red-700 rounded hover:bg-red-900">
                    <svg viewBox="0 -0.5 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                        <g><path d="M11.75 9.874C11.75 10.2882 12.0858 10.624 12.5 10.624C12.9142 10.624 13.25 10.2882 13.25 9.874H11.75ZM13.25 4C13.25 3.58579 12.9142 3.25 12.5 3.25C12.0858 3.25 11.75 3.58579 11.75 4H13.25ZM9.81082 6.66156C10.1878 6.48991 10.3542 6.04515 10.1826 5.66818C10.0109 5.29121 9.56615 5.12478 9.18918 5.29644L9.81082 6.66156ZM5.5 12.16L4.7499 12.1561L4.75005 12.1687L5.5 12.16ZM12.5 19L12.5086 18.25C12.5029 18.25 12.4971 18.25 12.4914 18.25L12.5 19ZM19.5 12.16L20.2501 12.1687L20.25 12.1561L19.5 12.16ZM15.8108 5.29644C15.4338 5.12478 14.9891 5.29121 14.8174 5.66818C14.6458 6.04515 14.8122 6.48991 15.1892 6.66156L15.8108 5.29644ZM13.25 9.874V4H11.75V9.874H13.25ZM9.18918 5.29644C6.49843 6.52171 4.7655 9.19951 4.75001 12.1561L6.24999 12.1639C6.26242 9.79237 7.65246 7.6444 9.81082 6.66156L9.18918 5.29644ZM4.75005 12.1687C4.79935 16.4046 8.27278 19.7986 12.5086 19.75L12.4914 18.25C9.08384 18.2892 6.28961 15.5588 6.24995 12.1513L4.75005 12.1687ZM12.4914 19.75C16.7272 19.7986 20.2007 16.4046 20.2499 12.1687L18.7501 12.1513C18.7104 15.5588 15.9162 18.2892 12.5086 18.25L12.4914 19.75ZM20.25 12.1561C20.2345 9.19951 18.5016 6.52171 15.8108 5.29644L15.1892 6.66156C17.3475 7.6444 18.7376 9.79237 18.75 12.1639L20.25 12.1561Z" fill="#ffffff"></path></g>
                    </svg>
                </button>
            </header>

            <main class="p-10">
                <h1 class="pb-10 text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">Reminders Home Page</h1>

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
                    <div v-for="(reminder, index) in reminders" :key="reminder.id || index" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">

                        <!-- Action Buttons -->
                        <div class="w-4.5 float-right">

                            <!-- View Button -->
                            <button @click="openViewModal(reminder)" class="text-green-500 hover:text-green-700 mr-4">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>

                            <!-- Edit Button -->
                            <button @click="openEditModal(reminder)" class="text-blue-500 hover:text-blue-700 mr-4">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34a1.003 1.003 0 00-1.42 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                </svg>
                            </button>

                            <!-- Delete Button -->
                            <button @click="openDeleteModal(reminder.id)" class="text-red-500 hover:text-red-700">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>

                        </div>

                        <h3 class="text-xl font-semibold text-gray-800 dark:text-white">{{ reminder.title }}</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ descriptionLimit(reminder.description, 250) }}</p>

                        <p class="mt-10 text-sm text-gray-500 dark:text-gray-300"><b>Event is:</b> {{ formatDate(reminder.event_at) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-300"><b>Reminder is:</b> {{ formatDate(reminder.remind_at) }}</p>
                    </div>
                </div>
            </main>

            <!-- Adding Reminder Button -->
            <button @click="openModal" class="fixed bottom-6 right-6 bg-black hover:bg-gray-800 text-white rounded-full w-16 h-16 flex items-center justify-center shadow-lg focus:outline-none" aria-label="Add Reminder">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>

            <!-- View Modal -->
            <transition name="modal">
                <div v-if="showViewModal" @click="closeViewModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <div @click.stop class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-11/12 max-w-md p-6 max-h-screen overflow-y-auto">
                        <div class="mb-4">
                            <p class="text-2xl mb-4 text-gray-800 dark:text-white">{{ currentReminder.title }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="mb-4 text-gray-800 dark:text-gray-400">{{ currentReminder.description }}</p>
                        </div>
                        <div class="mt-10">
                            <p class="font-bold tracking-tight text-gray-900 dark:text-gray-200">Event is: {{ formatDate(currentReminder.event_at) }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="mb-4 font-bold tracking-tight text-gray-900 dark:text-gray-200">Reminder is: {{ formatDate(currentReminder.remind_at) }}</p>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" @click="closeViewModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </transition>

            <transition name="modal">
                <!-- Edit / Add Modal -->
                <div v-if="showModal" @click="closeModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <div @click.stop class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-11/12 max-w-md p-6">
                        <h2 class="text-2xl mb-4 text-gray-800 dark:text-white">
                            {{ isEditing ? 'Edit Reminder' : 'Add New Reminder' }}
                        </h2>
                        <form @submit.prevent="isEditing ? updateReminder() : submitForm()">
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-gray-300 mb-2" for="title">Title</label>
                                <input type="text" id="title" v-model="form.title" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300 dark:bg-gray-700 dark:text-white" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-gray-300 mb-2" for="description">Description</label>
                                <textarea id="description" v-model="form.description" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300 dark:bg-gray-700 dark:text-white" rows="4"></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-gray-300 mb-2" for="event_at">When is the Event?</label>
                                <input type="datetime-local" id="event_at" v-model="form.event_at" :min="minDateTime" step="1" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300 dark:bg-gray-700 dark:text-white" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-gray-300 mb-2" for="remind_at">When to Remind You?</label>
                                <input type="datetime-local" id="remind_at" v-model="form.remind_at" :min="minDateTime" step="1" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300 dark:bg-gray-700 dark:text-white" required>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" @click="closeModal" class="px-4 py-2 mr-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    {{ isEditing ? 'Update' : 'Add' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </transition>

            <!-- Delete Modal -->
            <transition name="modal">
                <div v-if="showDeleteModal" @click="closeDeleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <div @click.stop class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-11/12 max-w-md p-6">
                        <h2 class="text-xl mb-4 text-gray-800 dark:text-white">Confirm Deletion</h2>
                        <p class="mb-6 text-gray-700 dark:text-gray-300">Are you sure you want to delete this reminder?</p>
                        <div class="flex justify-end">
                            <button type="button" @click="closeDeleteModal" class="px-4 py-2 mr-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="button" @click="deleteReminder" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" :disabled="deleting">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
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
                remind_at: '',
                event_at: '',
            },

            // Editing state
            isEditing: false,
            currentReminderId: null,

            // View modal
            showViewModal: false,
            currentReminder: {},

            // Confirmation for delete
            showDeleteModal: false,
            reminderIdToDelete: null,
            deleting: false,
        };
    },
    computed: {
        // Minimum date time for the datetime local input (current date and time)
        minDateTime() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`;
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
        // Getting the index endpoint for reminders
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
                        // Token refresh failed or user is unauthorized, remove token and user data
                        this.error = 'Session expired. Redirecting to login.';
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
            } finally {
                this.loading = false;
            }
        },
        // Clearing tokens on logout
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
                // Even if the API call fails, clear local tokens and redirect
                localStorage.removeItem('access_token');
                localStorage.removeItem('refresh_token');
                localStorage.removeItem('user');
                this.$router.push({ name: 'login' });
            }
        },

        // Converting unix timestamp to make it compatible with input
        formatDate(timestamp) {
            // Convert Unix timestamp to milliseconds
            const date = new Date(timestamp * 1000);
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            return date.toLocaleDateString(undefined, options);
        },
        // Converting the timestamp unix code to input date time
        convertTimestampToInput(timestamp) {
            const date = new Date(timestamp * 1000);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        },
        // Cleaning up description to have 250 char, except on show endpoint / view modal
        descriptionLimit(text, limit) {
            if (text.length > limit) {
                return text.substring(0, limit) + '...';
            }
            return text;
        },

        // Open Add Modal
        openModal() {
            this.showModal = true;
        },
        // Close Add Modal
        closeModal() {
            this.showModal = false;
            this.isEditing = false;
            this.currentReminderId = null;
            this.resetForm();
        },

        // Form submission
        async submitForm() {
            // Proceed only if not editing
            if (this.isEditing) return;

            // Validate form inputs
            if (!this.form.title || !this.form.remind_at || !this.form.event_at) {
                this.error = 'Please fill in all fields.';
                return;
            }

            // Convert remind_at and event_at to Unix timestamp
            const dateTimeRemindAt = new Date(this.form.remind_at);
            const unixTimestampRemindAt = Math.floor(dateTimeRemindAt.getTime() / 1000);
            const dateTimeEventAt = new Date(this.form.event_at);
            const unixTimestampEventAt = Math.floor(dateTimeEventAt.getTime() / 1000);

            const formData = {
                ...this.form,
                remind_at: unixTimestampRemindAt,
                event_at: unixTimestampEventAt,
            };

            try {
                // Send form data to the backend API
                const response = await this.$api.post('/api/reminders', formData);

                if (response.data.ok) {
                    // Successfully added the reminder
                    const newReminder = response.data.data;
                    newReminder.description = this.descriptionLimit(newReminder.description, 250);

                    this.reminders.push(newReminder);
                    this.closeModal();
                } else {
                    this.error = response.data.msg || 'Failed to add reminder.';
                }
            } catch (error) {
                if (error.response) {
                    if (error.response.status === 401) {
                        // Handle unauthorized error
                        this.error = 'Your session has expired. Please log in again.';
                        // Redirect to log in
                        this.$router.push({ name: 'login' });
                    } else if (error.response.status === 422) {
                        // Handle validation errors
                        const errors = error.response.data.errors;
                        // Display validation errors
                        this.error = Object.values(errors).flat().join(' ');
                    } else {
                        this.error = error.response.data.msg || 'Failed to add reminder.';
                    }
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
            this.form.remind_at = '';
            this.form.event_at = '';
            this.error = '';
        },

        // Open Edit Modal
        async openEditModal(reminder) {
            try {
                const response = await this.$api.get(`/api/reminders/${reminder.id}`);
                if (response.data.ok) {
                    this.currentReminder = response.data.data;
                    this.isEditing = true;
                    this.currentReminderId = reminder.id;
                    this.form.title = this.currentReminder.title;
                    this.form.description = this.currentReminder.description;
                    this.form.remind_at = this.convertTimestampToInput(this.currentReminder.remind_at);
                    this.form.event_at = this.convertTimestampToInput(this.currentReminder.event_at);
                    this.showModal = true;
                } else {
                    this.error = response.data.msg || 'Failed to fetch reminder details.';
                }
            } catch (error) {
                this.error = 'An error occurred while fetching the reminder.';
                console.error('Edit Reminder Error:', error);
            }
        },
        // Update reminder
        async updateReminder() {
            // Validate form inputs
            if (!this.form.title || !this.form.remind_at || !this.form.event_at) {
                this.error = 'Please fill in all fields.';
                return;
            }

            // Convert dates to Unix timestamps
            const dateTimeRemindAt = new Date(this.form.remind_at);
            const unixTimestampRemindAt = Math.floor(dateTimeRemindAt.getTime() / 1000);
            const dateTimeEventAt = new Date(this.form.event_at);
            const unixTimestampEventAt = Math.floor(dateTimeEventAt.getTime() / 1000);

            const formData = {
                title: this.form.title,
                description: this.form.description,
                remind_at: unixTimestampRemindAt,
                event_at: unixTimestampEventAt,
            };

            try {
                const response = await this.$api.put(`/api/reminders/${this.currentReminderId}`, formData);

                if (response.data.ok) {
                    // Update the reminder in the list
                    const index = this.reminders.findIndex(r => r.id === this.currentReminderId);
                    if (index !== -1) {
                        const updateReminder = { ...response.data.data };
                        updateReminder.description = this.descriptionLimit(updateReminder.description, 250);

                        this.$set(this.reminders, index, updateReminder);
                    }
                    this.closeModal();
                } else {
                    this.error = response.data.msg || 'Failed to update reminder.';
                }
            } catch (error) {
                if (error.response) {
                    if (error.response.status === 401) {
                        this.error = 'Your session has expired. Please log in again.';
                        this.$router.push({ name: 'login' });
                    } else if (error.response.status === 422) {
                        const errors = error.response.data.errors;
                        this.error = Object.values(errors).flat().join(' ');
                    } else {
                        this.error = error.response.data.msg || 'An error occurred.';
                    }
                } else if (error.request) {
                    this.error = 'No response from the server. Please try again later.';
                } else {
                    this.error = 'An unexpected error occurred.';
                }
                console.error('Error:', error);
            }
        },

        // Open View Modal
        async openViewModal(reminder) {
            try {
                const response = await this.$api.get(`/api/reminders/${reminder.id}`);
                if (response.data.ok) {
                    this.currentReminder = response.data.data;
                    this.showViewModal = true;
                } else {
                    this.error = response.data.msg || 'Failed to fetch reminder details.';
                }
            } catch (error) {
                this.error = 'An error occurred while fetching the reminder.';
                console.error('View Reminder Error:', error);
            }
        },
        // Close View Modal
        closeViewModal() {
            this.showViewModal = false;
            this.currentReminder = {};
        },


        // Open delete confirmation
        openDeleteModal(id) {
            this.showDeleteModal = true;
            this.reminderIdToDelete = id;
            this.error = '';
        },
        // Close the delete confirmation modal
        closeDeleteModal() {
            this.showDeleteModal = false;
            this.reminderIdToDelete = null;
        },
        // Delete reminder
        async deleteReminder() {
            if (!this.reminderIdToDelete) return;

            const id = this.reminderIdToDelete;
            if (!id) return;

            this.deleting = true;

            try {
                const response = await this.$api.delete(`/api/reminders/${id}`);

                if (response.data.ok) {
                    // Remove the reminder from the list
                    this.reminders = this.reminders.filter(r => r.id !== id);
                    this.closeDeleteModal();
                } else {
                    this.error = response.data.msg || 'Failed to delete reminder.';
                }
            } catch (error) {
                if (error.response) {
                    if (error.response.status === 401) {
                        this.error = 'Your session has expired. Please log in again.';
                        this.$router.push({ name: 'login' });
                    } else if (error.response.status === 422) {
                        const errors = error.response.data.errors;
                        this.error = Object.values(errors).flat().join(' ');
                    } else {
                        this.error = error.response.data.msg || 'An error occurred.';
                    }
                } else if (error.request) {
                    this.error = 'No response from the server. Please try again later.';
                } else {
                    this.error = 'An unexpected error occurred.';
                }
                console.error('Error:', error);
            } finally {
                this.deleting = false;
            }
        },


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

.modal-enter-active, .modal-leave-active {
    transition: opacity 0.3s;
}
.modal-enter, .modal-leave-to {
    opacity: 0;
}
</style>
