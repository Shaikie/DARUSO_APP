<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Case-insensitive text search across one or more columns.
 *
 * Uses LOWER() with LIKE rather than PostgreSQL's ILIKE so the same query works
 * on both the PostgreSQL application database and the SQLite database used by
 * the test suite.
 */
trait Searchable
{
    /**
     * @param  array<int, string>  $columns
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<covariant \Illuminate\Database\Eloquent\Model>
     */
    public function scopeSearch(Builder $query, ?string $term, array $columns): Builder
    {
        $term = trim((string) $term);

        if ($term === '' || $columns === []) {
            return $query;
        }

        $pattern = '%'.mb_strtolower($term).'%';
        $model = $query->getModel();

        return $query->where(function (Builder $inner) use ($columns, $pattern, $model): void {
            foreach ($columns as $column) {
                $inner->orWhereRaw(
                    'LOWER('.$model->qualifyColumn($column).') LIKE ?',
                    [$pattern]
                );
            }
        });
    }
}
