@props(['name', 'class' => 'size-4'])

@php
    // Map this app's icon names → Lucide icon names (data-lucide, kebab-case).
    // Rendered to SVG client-side by lucide createIcons() (see resources/js/app.js).
    $map = [
        'dashboard' => 'layout-dashboard',
        'users' => 'users',
        'settings' => 'settings',
        'search' => 'search',
        'sun' => 'sun',
        'moon' => 'moon',
        'menu' => 'menu',
        'chevron-down' => 'chevron-down',
        'chevrons-up-down' => 'chevrons-up-down',
        'logout' => 'log-out',
        'plus' => 'plus',
        'trending-up' => 'trending-up',
        'trending-down' => 'trending-down',
        'dots' => 'ellipsis',
        'user' => 'user',
        'mail' => 'mail',
        'check' => 'check',
        'x' => 'x',
        'circle-check' => 'circle-check',
        'alert-circle' => 'alert-circle',
        'triangle-alert' => 'triangle-alert',
        'info' => 'info',
        'file-spreadsheet' => 'file-spreadsheet',
    ];

    $lucide = $map[$name] ?? $name;
@endphp

<i data-lucide="{{ $lucide }}" {{ $attributes->merge(['class' => $class]) }}></i>
