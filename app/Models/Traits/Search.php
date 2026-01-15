<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Searchable;

trait Search
{
    use Searchable;

    /**
     * Build wildcards for MySQL FULLTEXT search.
     */
    private function buildWildCards(string $term): string
    {
        if ($term === "") {
            return $term;
        }

        // Strip MySQL reserved symbols
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term = str_replace($reservedSymbols, '', $term);

        $words = explode(' ', $term);
        foreach ($words as $idx => $word) {
            // Add operators so we can leverage the boolean mode of
            // fulltext indices.
            $words[$idx] = "+" . $word . "*";
        }
        $term = implode(' ', $words);
        return $term;
    }

    /**
     * Scope a query to search using MySQL FULLTEXT.
     * This is used as a fallback when Scout is not available.
     */
    public function scopeSearchFulltext(Builder $query, string $term): Builder
    {
        if (!isset($this->searchable) || empty($this->searchable)) {
            return $query;
        }

        $columns = implode(',', $this->searchable);

        // Boolean mode allows us to match john* for words starting with john
        // (https://dev.mysql.com/doc/refman/5.6/en/fulltext-boolean.html)
        $query->whereRaw(
            "MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)",
            [$this->buildWildCards($term)]
        );
        
        return $query;
    }

    /**
     * Perform a search using Scout if available, otherwise fallback to MySQL FULLTEXT.
     */
    public static function searchWithFallback(string $query): mixed
    {
        try {
            // Try to use Scout first
            if (config('scout.driver') !== null) {
                return static::search($query);
            }
        } catch (\Exception $e) {
            // Scout not available, fall through to FULLTEXT
        }

        // Fallback to MySQL FULLTEXT search
        return static::query()->searchFulltext($query);
    }

    /**
     * Get the indexable data array for the model.
     * This is used by Scout for indexing.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        // Only include searchable fields if defined
        if (isset($this->searchable) && !empty($this->searchable)) {
            $array = array_intersect_key($array, array_flip($this->searchable));
        }

        return $array;
    }
}
