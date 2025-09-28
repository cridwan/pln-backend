<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class FilteredScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $query, Model $model): Builder
    {
        $filters = request()->get("filters");
        if (!$filters || !is_array($filters)) {
            return $query;
        }

        foreach ($filters as $filter) {
            $column = $filter['column'] ?? null;
            $operator = strtoupper($filter['operator'] ?? 'EQ');
            $value = $filter['value'] ?? null;
            $group = strtoupper($filter['group'] ?? 'AND');

            if (!$column)
                continue;
            if ($value == "undefined")
                continue;

            $callback = $group === 'OR' ? 'orWhere' : 'where';

            $segments = explode('.', $column);

            if (count($segments) == 2) {
                [$relation, $relationColumn] = explode('.', $column, 2);

                if (method_exists($model, $relation)) {
                    $query->whereHas($relation, function ($q) use ($relationColumn, $operator, $value) {
                        self::applyOperator($q, $relationColumn, $operator, $value);
                    });
                }
            } else if (count($segments) > 2) {
                $field = array_pop($segments); // ambil nama kolom terakhir
                $relation = implode('.', $segments); // gabungkan sisa jadi relasi

                if (method_exists($model, explode('.', $relation)[0])) {
                    $query->whereHas($relation, function ($q) use ($field, $operator, $value) {
                        self::applyOperator($q, $field, $operator, $value);
                    });
                }
            } else {
                $validColumns = Schema::connection($model->getConnectionString())->getColumnListing($model->getTable());
                if (in_array($column, $validColumns)) {
                    self::applyOperator($query, $column, $operator, $value, $callback);
                }
            }
        }
        return $query;
    }

    protected static function applyOperator(Builder $query, string $column, string $operator, mixed $value, string $callback = 'where'): void
    {
        switch ($operator) {
            case 'EQ':
                $query->$callback($column, '=', $value);
                break;
            case 'NEQ':
                $query->$callback($column, '!=', $value);
                break;
            case 'GT':
                $query->$callback($column, '>', $value);
                break;
            case 'GTE':
                $query->$callback($column, '>=', $value);
                break;
            case 'LT':
                $query->$callback($column, '<', $value);
                break;
            case 'LTE':
                $query->$callback($column, '<=', $value);
                break;
            case 'LIKE':
                $query->$callback($column, 'LIKE', "%$value%");
                break;
            case 'IS_NULL':
                $query->whereNull($column);
                break;
            case 'NOT_NULL':
                $query->whereNotNull($column);
                break;
            case 'IN':
                $query->$callback(function ($q) use ($column, $value) {
                    $q->whereIn($column, is_array($value) ? $value : explode(',', $value));
                });
                break;
            default:
                $query->$callback($column, '=', $value);
        }
    }
}
