<?php

namespace App\Interfaces;

interface ApiResource
{
    public function index();

    public function store();

    public function update();

    public function delete();
}
