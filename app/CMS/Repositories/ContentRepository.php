<?php

namespace App\CMS\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ContentRepository
{
    protected Builder $query;

    protected string $modelClass;

    protected ?string $cacheKey = null;

    protected ?int $cacheTtl = null;

    protected bool $bypassCache = false;

    /**
     * Create a new repository instance.
     *
     * @param  string  $modelClass  Fully qualified model class name
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->query = $this->newQuery();
    }

    /**
     * Create a fresh query builder instance.
     */
    protected function newQuery(): Builder
    {
        return (new $this->modelClass)->newQuery();
    }

    /**
     * Reset the query builder to a fresh state.
     */
    protected function resetQuery(): void
    {
        $this->query = $this->newQuery();
        $this->cacheKey = null;
        $this->cacheTtl = null;
        $this->bypassCache = false;
    }

    /**
     * Find model by ID.
     */
    public function find(int $id): ?Model
    {
        return $this->executeQuery(fn () => $this->query->find($id));
    }

    /**
     * Find model by slug.
     */
    public function findBySlug(string $slug): ?Model
    {
        return $this->executeQuery(fn () => $this->query->where('slug', $slug)->first());
    }

    /**
     * Get all models.
     */
    public function all(): Collection
    {
        return $this->executeQuery(fn () => $this->query->get());
    }

    /**
     * Get paginated results.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $paginator = $this->query->paginate($perPage);
        $this->resetQuery();

        return $paginator;
    }

    /**
     * Get query results.
     */
    public function get(): Collection
    {
        return $this->executeQuery(fn () => $this->query->get());
    }

    /**
     * Add where clause to query.
     */
    public function where(string $column, mixed $operator = null, mixed $value = null): self
    {
        $this->query->where($column, $operator, $value);

        return $this;
    }

    /**
     * Add whereIn clause to query.
     */
    public function whereIn(string $column, array $values): self
    {
        $this->query->whereIn($column, $values);

        return $this;
    }

    /**
     * Add orderBy clause to query.
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query->orderBy($column, $direction);

        return $this;
    }

    /**
     * Eager load relationships.
     */
    public function with(array|string $relations): self
    {
        $this->query->with($relations);

        return $this;
    }

    /**
     * Create a new model.
     */
    public function create(array $data): Model
    {
        $model = $this->newQuery()->create($data);
        $this->resetQuery();

        return $model;
    }

    /**
     * Update an existing model.
     */
    public function update(int $id, array $data): bool
    {
        $model = $this->newQuery()->find($id);

        if (! $model) {
            $this->resetQuery();

            return false;
        }

        $updated = $model->update($data);
        $this->resetQuery();

        return $updated;
    }

    /**
     * Delete a model.
     */
    public function delete(int $id): bool
    {
        $model = $this->newQuery()->find($id);

        if (! $model) {
            $this->resetQuery();

            return false;
        }

        $deleted = $model->delete();
        $this->resetQuery();

        return (bool) $deleted;
    }

    /**
     * Count total models.
     */
    public function count(): int
    {
        $count = $this->query->count();
        $this->resetQuery();

        return $count;
    }

    /**
     * Enable caching for the next query.
     *
     * @param  string  $key  Cache key
     * @param  int  $ttl  Time to live in seconds
     */
    public function cache(string $key, int $ttl = 3600): self
    {
        $this->cacheKey = $key;
        $this->cacheTtl = $ttl;

        return $this;
    }

    /**
     * Bypass cache for the next query.
     */
    public function fresh(): self
    {
        $this->bypassCache = true;

        return $this;
    }

    /**
     * Execute query with optional caching.
     */
    protected function executeQuery(callable $callback): mixed
    {
        $shouldCache = config('cms.cache.enabled', true)
            && $this->cacheKey
            && ! $this->bypassCache;

        if ($shouldCache) {
            $result = Cache::remember(
                $this->cacheKey,
                $this->cacheTtl,
                $callback
            );
        } else {
            $result = $callback();
        }

        $this->resetQuery();

        return $result;
    }
}
