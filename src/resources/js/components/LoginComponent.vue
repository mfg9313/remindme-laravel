<template>
    <div class="login-container dark:bg-gray-900">
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
            <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                        Welcome!
                    </h1>
                    <p class="text-md font-bold leading-tight tracking-tight text-gray-500 md:text-md dark:text-white">
                        If you have an account, please login below.
                    </p>
                    <form class="space-y-4 md:space-y-6" @submit.prevent="login">
                        <div>
                            <input
                                type="email"
                                v-model="email"
                                name="email"
                                id="email"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Email Address"
                                required
                            >
                        </div>
                        <div>
                            <input
                                type="password"
                                v-model="password"
                                name="password"
                                id="password"
                                placeholder="Password"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                required
                            >
                        </div>
                        <div v-if="error" class="text-red-500 text-sm">
                            {{ error }}
                        </div>
                        <button
                            type="submit"
                            :disabled="loading"
                            class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
                        >
                            <span v-if="loading">Signing in...</span>
                            <span v-else>Sign in</span>
                        </button>
                        <p class="text-sm font-light text-gray-500 dark:text-gray-400"></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "LoginComponent",
    data() {
        return {
            email: '',
            password: '',
            error: '',
            loading: false
        };
    },
    methods: {
        async login() {
            this.error = '';
            this.loading = true;

            // Basic front-end validation
            if (!this.email || !this.password) {
                this.error = 'Please enter both email and password.';
                this.loading = false;
                return;
            }

            try {
                const response = await this.$api.post('/api/session', {
                    email: this.email,
                    password: this.password
                });

                if (response.data.ok) {
                    const { access_token, refresh_token, user } = response.data.data;

                    // Store tokens securely
                    localStorage.setItem('access_token', access_token);
                    localStorage.setItem('refresh_token', refresh_token);

                    // Store user information
                    localStorage.setItem('user', JSON.stringify(user));

                    // Update Axios defaults
                    this.$api.defaults.headers['Authorization'] = `Bearer ${access_token}`;

                    // Redirect to the reminders home page
                    this.$router.push({name: 'home'});
                } else {
                    this.error = response.data.msg || 'Login failed. Please try again.';
                }
            } catch (error) {
                if (error.response) {
                    if (error.response.status === 429) {
                        this.error = 'Too many login attempts. Please try again later.';
                    } else {
                        // Server responded with a status other than 2xx
                        this.error = error.response.data.msg || 'Login failed. Please check your credentials.';
                    }
                } else if (error.request) {
                    // Request was made but no response received
                    this.error = 'No response from the server. Please try again later.';
                } else {
                    // Something else caused the error
                    this.error = 'An unexpected error occurred.';
                }
                console.error('Login Error:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
