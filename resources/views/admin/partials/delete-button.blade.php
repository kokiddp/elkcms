{{--
    Delete Button with Confirmation

    Usage:
    @include('admin.partials.delete-button', [
        'route' => route('admin.content.destroy', ['modelType' => 'post', 'id' => 1]),
        'message' => 'Are you sure you want to delete this item?',
        'label' => 'Delete',
        'size' => 'sm', // sm, md, lg
        'class' => 'additional-classes',
    ])
--}}

<form method="POST" action="{{ $route }}" class="d-inline {{ $formClass ?? '' }}"
      onsubmit="return confirm('{{ $message ?? 'Are you sure you want to delete this item? This action cannot be undone.' }}');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-{{ $variant ?? 'danger' }} {{ isset($size) ? 'btn-' . $size : '' }} {{ $class ?? '' }}">
        @if(!isset($noIcon) || !$noIcon)
            <i class="bi bi-trash"></i>
        @endif
        {{ $label ?? 'Delete' }}
    </button>
</form>
