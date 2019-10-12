<?php

namespace Dillingham\Searchable;

use Illuminate\Support\Facades\DB;

trait Searchable
{
    public $searchable = ['search'];

    public function scopeSearch($query, $terms = null)
    {
        if (is_null($terms)) {
            return $query;
        }

        $searchable = implode(',', $this->searchable);

        $terms = $this->searchSanitize($terms);

        foreach (explode(' ', $terms) as $term) {
            $terms = (strlen($term) > 2)
                ? str_replace($term, "+$term*", $terms)
                : str_replace($term, '', $terms);
        }

        $query->whereRaw("MATCH($searchable) AGAINST(? IN BOOLEAN MODE)", [$terms]);

        return $query;
    }

    public function scopeSearchable($query)
    {
        $query->search(request('q'));
    }

    public function appendSearch($content)
    {
        if (!$this->exists) {
            $this->search = $this->searchSanitize($content);
        }

        $this->search = str_replace(
            $this->searchSanitize($content),
            $this->searchSanitize($content),
            $this->search
        );
    }

    public function fillSearchUsing($column)
    {
        if (!$this->exists) {
            $this->search = $this->searchSanitize($this->$column);
        }

        if ($this->isDirty($column) && !is_null($this->getOriginal($column))) {
            $this->search = str_replace(
                $this->searchSanitize($this->getOriginal($column)),
                $this->searchSanitize($this->$column),
                $this->search
            );
        }
    }

    public function searchSanitize($value)
    {
        $value = strtolower($value);
        $value = preg_replace('/[^\w ]/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    public static function searchIndex()
    {
        $model = new self;
        $table = $model->getTable();
        $columns = implode(',', $model->searchable);

        DB::statement("ALTER TABLE $table ADD FULLTEXT SEARCHABLE($columns)");
    }
}
