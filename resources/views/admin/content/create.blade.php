@extends('admin.layouts.app')

@section('title', 'Create ' . ($label ?? 'Content'))
@section('page-title', 'Create ' . ($label ?? 'Content'))

@section('content')
@php
    // Check if any field is a pagebuilder type
    $hasPageBuilder = collect($metadata['fields'] ?? [])->contains(fn($field) => ($field['type'] ?? null) === 'pagebuilder');
@endphp

<form method="POST" action="{{ route('admin.content.store', ['modelType' => $modelType]) }}">
    @csrf
    
    <div class="row">
        <div class="{{ $hasPageBuilder ? 'col-12' : 'col-md-8' }}">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">New {{ $label ?? 'Content' }}</h5>
                </div>
                <div class="card-body">
                    @include('admin.content.form')

                    @if(!$hasPageBuilder)
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.content.index', ['modelType' => $modelType]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Create
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="{{ $hasPageBuilder ? 'col-12 mt-4' : 'col-md-4' }}">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Publishing Options</h6>
                </div>
                <div class="card-body">
                    <div class="{{ $hasPageBuilder ? 'd-flex gap-4 flex-wrap' : '' }}">
                        <div class="{{ $hasPageBuilder ? 'flex-fill' : 'mb-3' }}" style="{{ $hasPageBuilder ? 'min-width: 250px' : '' }}">
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

                        @if(!$hasPageBuilder)
                            <div class="alert alert-info mb-0">
                                <small><i class="bi bi-info-circle"></i> Draft content is not visible to the public.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        @if($hasPageBuilder)
            <div class="col-12 mt-4">
                <div class="d-flex justify-content-between" style="padding-top: 1.5rem; border-top: 2px solid #e0e0e0;">
                    <a href="{{ route('admin.content.index', ['modelType' => $modelType]) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Create
                    </button>
                </div>
            </div>
        @endif
    </div>
</form>
@endsection
