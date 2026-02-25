@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
        
        <form id="loginForm" x-data="loginForm()" @submit.prevent="submit">
            <!-- Alert -->
            <div x-show="error" x-text="error" 
                 class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" x-model="form.email" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" x-model="form.password" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <!-- Submit -->
            <button type="submit" :disabled="loading"
                    class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50">
                <span x-show="!loading">Login</span>
                <span x-show="loading">Loading...</span>
            </button>
        </form>
    </div>
</div>

<script>
function loginForm() {
    return {
        form: {
            email: '',
            password: ''
        },
        loading: false,
        error: '',

        async submit() {
            this.loading = true;
            this.error = '';

            try {
                console.log('Attempting login...');
                const response = await window.api.login(this.form.email, this.form.password);
                console.log('Login response:', response.data);
                
                // Simpan token jika ada
                if (response.data.access_token) {
                    const token = response.data.access_token;
                    localStorage.setItem('auth_token', token);
                    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                    
                    console.log('Token saved successfully');
                    
                    // Small delay to ensure token is saved
                    await new Promise(resolve => setTimeout(resolve, 100));
                    
                    // Redirect ke dashboard
                    window.location.href = '/dashboard';
                } else {
                    this.error = 'No access token received';
                }
            } catch (err) {
                console.error('Login error:', err);
                this.error = err.response?.data?.message || 'Login failed';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection