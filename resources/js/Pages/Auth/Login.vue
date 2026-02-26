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

    <div class="min-h-screen flex items-center justify-center bg-base-100">
        <div class="w-full max-w-md px-4">
            <div class="bg-base-200 border border-neutral/30 rounded-md p-6">
                <!-- Logo/Title -->
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-primary/20 border-2 border-primary/50 rounded flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-mono font-bold tracking-wider uppercase mb-1">
                        PRESENSI SYSTEM
                    </h2>
                    <p class="text-xs font-mono text-base-content/50 uppercase tracking-wider">Access Control</p>
                </div>

                <!-- Error Message -->
                <div v-if="$page.props.flash.error" class="bg-error/10 border border-error/30 rounded p-3 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-error shrink-0" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs font-mono text-error">{{ $page.props.flash.error }}</span>
                </div>
                
                <form @submit.prevent="submit">
                    <!-- Email Field -->
                    <div class="mb-4">
                        <label class="block text-xs font-mono font-semibold tracking-wider uppercase text-base-content/70 mb-2">
                            User ID
                        </label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            placeholder="user@system.local" 
                            class="w-full bg-base-300 border px-3 py-2 rounded text-sm font-mono focus:outline-none focus:border-primary transition-colors"
                            :class="form.errors.email ? 'border-error' : 'border-neutral/30'"
                            required
                            autofocus
                        />
                        <p v-if="form.errors.email" class="text-xs font-mono text-error mt-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-6">
                        <label class="block text-xs font-mono font-semibold tracking-wider uppercase text-base-content/70 mb-2">
                            Access Code
                        </label>
                        <input 
                            v-model="form.password" 
                            type="password" 
                            placeholder="••••••••" 
                            class="w-full bg-base-300 border px-3 py-2 rounded text-sm font-mono focus:outline-none focus:border-primary transition-colors"
                            :class="form.errors.password ? 'border-error' : 'border-neutral/30'"
                            required
                        />
                        <p v-if="form.errors.password" class="text-xs font-mono text-error mt-1">{{ form.errors.password }}</p>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-primary hover:bg-primary/90 text-primary-content font-mono font-bold text-sm tracking-wider uppercase py-2.5 border border-primary rounded transition-colors disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin mr-2"></span>
                        {{ form.processing ? 'AUTHENTICATING...' : 'AUTHENTICATE' }}
                    </button>
                </form>

                <!-- Footer -->
                <div class="text-center mt-6 pt-4 border-t border-neutral/30">
                    <div class="flex items-center justify-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-success animate-pulse"></div>
                        <p class="text-[10px] font-mono text-base-content/40 uppercase tracking-wider">System Online</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
