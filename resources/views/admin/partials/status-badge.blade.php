{{--
    Status Badge Component

    Usage:
    @include('admin.partials.status-badge', ['status' => 'published'])
    @include('admin.partials.status-badge', ['status' => 'draft', 'label' => 'In Draft'])
--}}

@php
    $statusColors = [
        'published' => 'success',
        'draft' => 'warning',
        'archived' => 'secondary',
        'pending' => 'info',
        'active' => 'success',
        'inactive' => 'secondary',
    ];

    $color = $statusColors[$status] ?? 'info';
    $displayLabel = $label ?? ucfirst($status);
@endphp

<span class="badge bg-{{ $color }} {{ $class ?? '' }}">{{ $displayLabel }}</span>
