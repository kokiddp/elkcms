@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="row g-4">
        <!-- Total Content Widget -->
        <div class="col-md-3">
            <div class="dashboard-widget">
                <div class="widget-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="widget-value">{{ $stats['total_content'] }}</div>
                <div class="widget-label">Total Content</div>
            </div>
        </div>

        <!-- Total Users Widget -->
        <div class="col-md-3">
            <div class="dashboard-widget">
                <div class="widget-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-people"></i>
                </div>
                <div class="widget-value">{{ $stats['total_users'] }}</div>
                <div class="widget-label">Total Users</div>
            </div>
        </div>

        <!-- Total Translations Widget -->
        <div class="col-md-3">
            <div class="dashboard-widget">
                <div class="widget-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-translate"></i>
                </div>
                <div class="widget-value">{{ $stats['total_translations'] }}</div>
                <div class="widget-label">Total Translations</div>
            </div>
        </div>

        <!-- Active Languages Widget -->
        <div class="col-md-3">
            <div class="dashboard-widget">
                <div class="widget-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-globe"></i>
                </div>
                <div class="widget-value">{{ count($stats['translation_progress']) }}</div>
                <div class="widget-label">Active Languages</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <!-- Translation Progress -->
        <div class="col-md-6">
            <div class="dashboard-widget">
                <h5 class="mb-3">Translation Progress</h5>
                @foreach($stats['translation_progress'] as $locale => $count)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-uppercase">{{ $locale }}</span>
                            <span class="text-muted">{{ $count }} translations</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" 
                                 role="progressbar" 
                                 style="width: {{ $count > 0 ? min(100, ($count / max(1, $stats['total_translations'])) * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Content -->
        <div class="col-md-6">
            <div class="dashboard-widget">
                <h5 class="mb-3">Recent Content</h5>
                @if($stats['recent_content']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($stats['recent_content'] as $content)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $content->title }}</h6>
                                        <small class="text-muted">
                                            {{ $content->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $content->status === 'published' ? 'success' : 'warning' }}">
                                        {{ ucfirst($content->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No content yet. <a href="#">Create your first post</a></p>
                @endif
            </div>
        </div>
    </div>

    <!-- Welcome Message for New Users -->
    @if($stats['total_content'] == 0)
        <div class="row g-4 mt-1">
            <div class="col-12">
                <div class="alert alert-info">
                    <h4 class="alert-heading">
                        <i class="bi bi-info-circle"></i> Welcome to ELKCMS!
                    </h4>
                    <p>Get started by creating your first content or exploring the features:</p>
                    <hr>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Create Content
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-translate"></i> Manage Translations
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-folder2"></i> Media Library
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
