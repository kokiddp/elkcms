@extends('admin.layouts.app')

@section('title', 'Create ' . ($label ?? 'Content'))
@section('page-title', 'Create ' . ($label ?? 'Content'))

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">New {{ $label ?? 'Content' }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.content.store', ['modelType' => $modelType]) }}">
                    @csrf
                    @include('admin.content.form')

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.content.index', ['modelType' => $modelType]) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create
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
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <small><i class="bi bi-info-circle"></i> Draft content is not visible to the public.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
