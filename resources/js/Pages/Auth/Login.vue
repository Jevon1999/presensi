<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login', {
        onSuccess: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Login" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 via-gray-200 to-gray-300">
        <div class="card w-96 bg-base-100 shadow-2xl">
            <div class="card-body">
                <!-- Logo/Title -->
                <div class="text-center mb-4">
                    <div class="w-16 h-16 bg-primary rounded-lg flex items-center justify-center mx-auto mb-3">
                        <span class="text-3xl">📋</span>
                    </div>
                    <h2 class="text-2xl font-bold text-neutral">
                        Presensi GI
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Login to continue</p>
                </div>

                <!-- Error Message -->
                <div v-if="$page.props.flash.error" class="alert alert-error shadow-lg mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $page.props.flash.error }}</span>
                </div>
                
                <form @submit.prevent="submit">
                    <!-- Email Field -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Email</span>
                        </label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            placeholder="email@example.com" 
                            class="input input-bordered w-full"
                            :class="{ 'input-error': form.errors.email }"
                            required
                            autofocus
                        />
                        <label v-if="form.errors.email" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.email }}</span>
                        </label>
                    </div>

                    <!-- Password Field -->
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-medium">Password</span>
                        </label>
                        <input 
                            v-model="form.password" 
                            type="password" 
                            placeholder="••••••••" 
                            class="input input-bordered w-full"
                            :class="{ 'input-error': form.errors.password }"
                            required
                        />
                        <label v-if="form.errors.password" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.password }}</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-control">
                        <button 
                            type="submit" 
                            class="btn btn-primary w-full"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                            {{ form.processing ? 'Logging in...' : 'Login' }}
                        </button>
                    </div>
                </form>

                <!-- Footer -->
                <div class="text-center mt-4 text-sm text-gray-500">
                    Attendance Management System
                </div>
            </div>
        </div>
    </div>
</template>
