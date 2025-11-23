<?php

namespace App\Core;

use App\Data\TemplateData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controllers\HasMiddleware;

interface BaseCore extends HasMiddleware
{
    /**
     * Summary of attributeExport
     * @return \App\Data\AttributeData[]
     */
    public function attributeExport(): array;
    /**
     * Summary of attributeExport
     * @return \App\Data\TemplateData
     */
    public function attributeTemplate(): TemplateData;

    /**
     * Summary of with
     * @return string[]
     */
    public function with(): array;
    /**
     * Summary of with
     * @return mixed[]
     */
    public function rules(): array;
    /**
     * Summary of with
     * @return string[]
     */
    public function search(): array;
    /**
     * Summary of with
     * @return string[]
     */
    public function order(): array;
    /**
     * Summary of with
     * @return string
     */
    public function model(): mixed;

    /**
     * Summary of query
     * @return mixed
     */
    public function query(): mixed;
}
