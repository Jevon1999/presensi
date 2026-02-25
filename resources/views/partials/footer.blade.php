{{-- 
    Footer Component
    - Copyright info
    - Links to terms, privacy, etc.
    - Keep it minimal for dashboard apps
--}}

<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between">
            
            {{-- Left: Copyright --}}
            <div class="text-center md:text-left">
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} Absensi App. All rights reserved.
                </p>
            </div>

            {{-- Right: Links --}}
            <div class="mt-4 md:mt-0">
                <nav class="flex justify-center md:justify-end space-x-6">
                    <a href="#" class="text-sm text-gray-500 hover:text-gray-700">
                        Privacy Policy
                    </a>
                    <a href="#" class="text-sm text-gray-500 hover:text-gray-700">
                        Terms of Service
                    </a>
                    <a href="#" class="text-sm text-gray-500 hover:text-gray-700">
                        Support
                    </a>
                </nav>
            </div>
        </div>

        {{-- Version info (optional for development) --}}
        @env('local')
            <div class="mt-4 text-center">
                <p class="text-xs text-gray-400">
                    Version 1.0.0 | Environment: {{ app()->environment() }}
                </p>
            </div>
        @endenv
    </div>
</footer>
