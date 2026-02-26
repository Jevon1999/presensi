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

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50">
        <div class="w-full max-w-md px-4">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-xl p-8">
                <!-- Logo/Title -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-600/20">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight mb-1">
                        Welcome Back
                    </h2>
                    <p class="text-sm text-gray-500">Sign in to your account to continue</p>
                </div>

                <!-- Error Message -->
                <div v-if="$page.props.flash.error" class="bg-red-50 border border-red-100 rounded-xl p-4 mb-6 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm text-red-600 font-medium">{{ $page.props.flash.error }}</span>
                </div>
                
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email Field -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Email Address
                        </label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            placeholder="you@company.com" 
                            class="w-full bg-gray-50 border px-4 py-3 rounded-xl text-sm font-medium placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                            :class="form.errors.email ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                            required
                            autofocus
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-500 mt-2 font-medium">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Password
                        </label>
                        <input 
                            v-model="form.password" 
                            type="password" 
                            placeholder="••••••••" 
                            class="w-full bg-gray-50 border px-4 py-3 rounded-xl text-sm font-medium placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                            :class="form.errors.password ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                            required
                        />
                        <p v-if="form.errors.password" class="text-xs text-red-500 mt-2 font-medium">{{ form.errors.password }}</p>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-3.5 rounded-xl transition-all shadow-lg shadow-blue-600/20 hover:shadow-xl hover:shadow-blue-600/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        :disabled="form.processing"
                    >
                        <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Signing in...' : 'Sign In' }}
                    </button>
                </form>

                <!-- Footer -->
                <div class="text-center mt-8 pt-6 border-t border-gray-100">
                    <p class="text-xs text-gray-400 font-medium">Attendance Management System © 2026</p>
                </div>
            </div>
        </div>
    </div>
</template>
