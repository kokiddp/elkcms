<?php

namespace App\Http\Controllers\Admin;

use App\CMS\Repositories\ContentRepository;
use App\CMS\Reflection\ModelScanner;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ContentController extends Controller
{
    protected ModelScanner $scanner;

    public function __construct(ModelScanner $scanner)
    {
        $this->scanner = $scanner;
    }

    /**
     * Display a listing of content for a specific model type.
     */
    public function index(string $modelType): View
    {
        $modelClass = $this->getModelClass($modelType);
        $repository = new ContentRepository($modelClass);
        $metadata = $this->scanner->scan($modelClass);
        
        $contents = $repository
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('admin.content.index', [
            'modelType' => $modelType,
            'modelClass' => $modelClass,
            'metadata' => $metadata,
            'label' => $metadata['contentModel']['label'] ?? 'Content',
            'contents' => $contents,
        ]);
    }

    /**
     * Show the form for creating new content.
     */
    public function create(string $modelType): View
    {
        $modelClass = $this->getModelClass($modelType);
        $metadata = $this->scanner->scan($modelClass);

        return view('admin.content.create', [
            'modelType' => $modelType,
            'modelClass' => $modelClass,
            'metadata' => $metadata,
            'label' => $metadata['contentModel']['label'] ?? 'Content',
        ]);
    }

    /**
     * Store a newly created content.
     */
    public function store(Request $request, string $modelType): RedirectResponse
    {
        $modelClass = $this->getModelClass($modelType);
        $metadata = $this->scanner->scan($modelClass);
        
        // Validate request
        $validated = $request->validate($this->getValidationRules($metadata));

        $repository = new ContentRepository($modelClass);
        
        // Set default status if not provided
        if (!isset($validated['status'])) {
            $validated['status'] = 'draft';
        }

        $content = $repository->create($validated);

        Log::info('Content created', [
            'model' => $modelType,
            'id' => $content->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.content.edit', ['modelType' => $modelType, 'id' => $content->id])
            ->with('success', 'Content created successfully!');
    }

    /**
     * Show the form for editing content.
     */
    public function edit(string $modelType, int $id): View
    {
        $modelClass = $this->getModelClass($modelType);
        $repository = new ContentRepository($modelClass);

        $content = $repository->find($id);
        
        if (!$content) {
            abort(404, 'Content not found');
        }

        $metadata = $this->scanner->scan($modelClass);

        return view('admin.content.edit', [
            'modelType' => $modelType,
            'modelClass' => $modelClass,
            'metadata' => $metadata,
            'label' => $metadata['contentModel']['label'] ?? 'Content',
            'content' => $content,
        ]);
    }

    /**
     * Update the specified content.
     */
    public function update(Request $request, string $modelType, int $id): RedirectResponse
    {
        $modelClass = $this->getModelClass($modelType);
        $metadata = $this->scanner->scan($modelClass);
        
        // Validate request
        $validated = $request->validate($this->getValidationRules($metadata));

        $repository = new ContentRepository($modelClass);
        $updated = $repository->update($id, $validated);

        if (!$updated) {
            return back()->with('error', 'Failed to update content.');
        }

        Log::info('Content updated', [
            'model' => $modelType,
            'id' => $id,
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.content.edit', ['modelType' => $modelType, 'id' => $id])
            ->with('success', 'Content updated successfully!');
    }

    /**
     * Remove the specified content.
     */
    public function destroy(string $modelType, int $id): RedirectResponse
    {
        $modelClass = $this->getModelClass($modelType);
        $repository = new ContentRepository($modelClass);

        $deleted = $repository->delete($id);

        if (!$deleted) {
            return back()->with('error', 'Failed to delete content.');
        }

        Log::info('Content deleted', [
            'model' => $modelType,
            'id' => $id,
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.content.index', ['modelType' => $modelType])
            ->with('success', 'Content deleted successfully!');
    }

    /**
     * Get the full class name for a model type.
     */
    protected function getModelClass(string $modelType): string
    {
        // Convert model type to class name (e.g., 'test-post' => 'TestPost')
        $className = str_replace('-', '', ucwords($modelType, '-'));
        $modelClass = "App\\CMS\\ContentModels\\{$className}";

        if (!class_exists($modelClass)) {
            abort(404, "Content model {$modelType} not found");
        }

        return $modelClass;
    }

    /**
     * Extract validation rules from metadata.
     */
    protected function getValidationRules(array $metadata): array
    {
        $rules = [];
        
        foreach ($metadata['fields'] as $fieldName => $field) {
            if (!empty($field['validation'])) {
                $rules[$fieldName] = $field['validation'];
            }
        }

        return $rules;
    }
}
