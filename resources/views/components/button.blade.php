{{--
    Reusable Button Component
    Usage: @include('components.button', ['text' => 'Submit', 'type' => 'primary'])
--}}

@php
    $type = $type ?? 'primary'; // primary, secondary, danger, success
    $size = $size ?? 'md'; // sm, md, lg
    $buttonType = $buttonType ?? 'button'; // button, submit, reset
    $class = $class ?? '';
    
    // Type variants
    $typeClasses = [
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
        'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
        'outline' => 'bg-white border-2 border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500',
    ];
    
    // Size variants
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
    ];
    
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    $finalClasses = $baseClasses . ' ' . ($typeClasses[$type] ?? $typeClasses['primary']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . $class;
@endphp

<button type="{{ $buttonType }}" 
        class="{{ $finalClasses }}"
        {{ $attributes ?? '' }}>
    @if(isset($icon))
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icon !!}
        </svg>
    @endif
    {{ $text ?? $slot }}
</button>
