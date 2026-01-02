@extends('admin.layouts.app')

@section('title', $label ?? 'Content')
@section('page-title', $label ?? 'Content')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ $label ?? 'Content' }}</h2>
    <a href="{{ route('admin.content.create', ['modelType' => $modelType]) }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New
    </a>
</div>

@if($contents->isEmpty())
    @include('admin.partials.empty-state', [
        'icon' => 'bi-file-earmark-text',
        'title' => 'No content found',
        'message' => 'Create your first ' . strtolower($label ?? 'content') . ' item.',
        'action' => route('admin.content.create', ['modelType' => $modelType]),
        'actionLabel' => 'Create First Item',
        'actionIcon' => 'bi-plus-circle',
    ])
@else
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contents as $content)
                        <tr>
                            <td>{{ $content->id }}</td>
                            <td>
                                <a href="{{ route('admin.content.edit', ['modelType' => $modelType, 'id' => $content->id]) }}" class="text-decoration-none">
                                    {{ $content->title ?? 'Untitled' }}
                                </a>
                            </td>
                            <td>
                                @include('admin.partials.status-badge', ['status' => $content->status])
                            </td>
                            <td>{{ $content->updated_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.content.edit', ['modelType' => $modelType, 'id' => $content->id]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    @include('admin.partials.delete-button', [
                                        'route' => route('admin.content.destroy', ['modelType' => $modelType, 'id' => $content->id]),
                                        'size' => 'sm',
                                        'variant' => 'outline-danger',
                                    ])
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $contents->links() }}
            </div>
        </div>
    </div>
@endif
@endsection
