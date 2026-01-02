{{--
    Reusable Card Component

    Usage:
    @include('admin.partials.card', [
        'title' => 'Card Title',
        'subtitle' => 'Optional subtitle',
        'actions' => '<a href="#">Action</a>', // Optional HTML for header actions
        'footer' => 'Footer content', // Optional footer content
    ])
        Card body content here
    @endslot
--}}

<div class="card {{ $class ?? '' }}">
    @if(isset($title) || isset($actions))
        <div class="card-header {{ isset($headerClass) ? $headerClass : 'd-flex justify-content-between align-items-center' }}">
            <div>
                @if(isset($title))
                    <h5 class="mb-0">{{ $title }}</h5>
                @endif
                @if(isset($subtitle))
                    <small class="text-muted">{{ $subtitle }}</small>
                @endif
            </div>
            @if(isset($actions))
                <div>{!! $actions !!}</div>
            @endif
        </div>
    @endif

    <div class="card-body {{ $bodyClass ?? '' }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="card-footer {{ $footerClass ?? '' }}">
            {!! $footer !!}
        </div>
    @endif
</div>
