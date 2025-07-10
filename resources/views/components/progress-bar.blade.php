@props([
    'percentage' => 0,
    'color' => 'blue',
    'label' => '',
    'showPercentage' => true,
])

@php
    $percentage = max(0, min(100, $percentage));
    $colorClasses = match($color) {
        'success', 'green' => 'bg-green-500',
        'warning', 'yellow' => 'bg-yellow-500',
        'danger', 'red' => 'bg-red-500',
        'info', 'blue' => 'bg-blue-500',
        'primary' => 'bg-indigo-500',
        'gray' => 'bg-gray-500',
        default => 'bg-blue-500',
    };
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($label)
        <div class="flex justify-between items-center mb-1">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
            @if($showPercentage)
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $percentage }}%</span>
            @endif
        </div>
    @endif
    
    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
        <div class="{{ $colorClasses }} h-2.5 rounded-full transition-all duration-300 ease-in-out" 
             style="width: {{ $percentage }}%"></div>
    </div>
</div>
