{{--
    Empty State Component

    Usage:
    @include('admin.partials.empty-state', [
        'icon' => 'bi-file-earmark-text',
        'title' => 'No items found',
        'message' => 'Create your first item to get started.',
        'action' => route('admin.items.create'),
        'actionLabel' => 'Create First Item',
    ])
--}}

<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi {{ $icon ?? 'bi-inbox' }} text-muted" style="font-size: 3rem;"></i>

        @if(isset($title))
            <h4 class="mt-3">{{ $title }}</h4>
        @endif

        @if(isset($message))
            <p class="text-muted mt-3 mb-4">{{ $message }}</p>
        @endif

        @if(isset($action))
            <a href="{{ $action }}" class="btn btn-primary">
                @if(isset($actionIcon))
                    <i class="bi {{ $actionIcon }}"></i>
                @endif
                {{ $actionLabel ?? 'Get Started' }}
            </a>
        @endif
    </div>
</div>
