@extends('admin.layouts.app')

@section('title', 'Edit ' . ($label ?? 'Content'))
@section('page-title', 'Edit ' . ($label ?? 'Content'))

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit: {{ $content->title ?? 'Untitled' }}</h5>
                <small class="text-muted">ID: {{ $content->id }}</small>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.content.update', ['modelType' => $modelType, 'id' => $content->id]) }}">
                    @csrf
                    @method('PUT')
                    @include('admin.content.form')

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.content.index', ['modelType' => $modelType]) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Publishing Options</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="draft" {{ old('status', $content->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $content->status) === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ old('status', $content->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label">Created</label>
                    <p class="text-muted small">{{ $content->created_at->format('Y-m-d H:i:s') }}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label">Last Updated</label>
                    <p class="text-muted small">{{ $content->updated_at->format('Y-m-d H:i:s') }}</p>
                </div>

                <hr>

                <form method="POST"
                      action="{{ route('admin.content.destroy', ['modelType' => $modelType, 'id' => $content->id]) }}"
                      onsubmit="return confirm('Are you sure you want to delete this item? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-trash"></i> Delete Content
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
