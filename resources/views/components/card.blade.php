{{--
    Reusable Card Component
    Usage: @include('components.card', ['title' => 'Card Title'])
--}}

@php
    $padding = $padding ?? 'p-6';
    $shadow = $shadow ?? 'shadow';
    $class = $class ?? '';
@endphp

<div class="bg-white rounded-lg {{ $shadow }} {{ $class }}">
    @if(isset($title) || isset($header))
        <div class="px-6 py-4 border-b border-gray-200">
            @if(isset($header))
                {{ $header }}
            @else
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            @endif
        </div>
    @endif
    
    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            {{ $footer }}
        </div>
    @endif
</div>
