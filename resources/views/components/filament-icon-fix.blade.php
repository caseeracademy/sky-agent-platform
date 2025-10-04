{{-- Filament Icon Size Fix Component --}}
{{-- This component ensures icons are properly sized --}}

@props([
    'icon' => null,
    'size' => 'md', // xs, sm, md, lg, xl
    'class' => '',
])

@php
    $sizeClasses = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-4 h-4', 
        'md' => 'w-5 h-5',
        'lg' => 'w-6 h-6',
        'xl' => 'w-8 h-8',
    ];
    
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $classes = "fi-icon {$sizeClass} {$class}";
@endphp

@if($icon)
    <x-filament::icon 
        :icon="$icon" 
        :class="$classes"
        {{ $attributes->merge(['class' => $classes]) }}
    />
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </div>
@endif
