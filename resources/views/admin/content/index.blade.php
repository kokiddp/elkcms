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
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-file-earmark-text text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-3 mb-4">No content found. Create your first {{ strtolower($label ?? 'content') }} item.</p>
            <a href="{{ route('admin.content.create', ['modelType' => $modelType]) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create First Item
            </a>
        </div>
    </div>
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
                                @if($content->status === 'published')
                                    <span class="badge bg-success">Published</span>
                                @elseif($content->status === 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @elseif($content->status === 'archived')
                                    <span class="badge bg-secondary">Archived</span>
                                @else
                                    <span class="badge bg-info">{{ ucfirst($content->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $content->updated_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.content.edit', ['modelType' => $modelType, 'id' => $content->id]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form method="POST"
                                          action="{{ route('admin.content.destroy', ['modelType' => $modelType, 'id' => $content->id]) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
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
