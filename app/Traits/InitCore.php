<?php

namespace App\Traits;

use App\Data\TemplateData;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;


trait InitCore
{
    public string $model;
    public array $order;
    public array $search;
    public array $attributeExport;
    public TemplateData $attributeTemplate;
    public array $with;
    public array $rules;

    use HasList, HasApiResource, ImportExportExcel, HasPagination;

    #[DoNotDiscover]
    public function setup()
    {
        $this->model = static::model();
        $this->order = static::order();
        $this->search = static::search();
        $this->attributeExport = static::attributeExport();
        $this->attributeTemplate = static::attributeTemplate();
        $this->with = static::with();
        $this->rules = static::rules();
    }

    #[DoNotDiscover]
    public function initCore()
    {
        $this->setup();
    }
}
