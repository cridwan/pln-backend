<?php

namespace App\Interfaces;


interface WithImportExcel
{
    public function mapping($data, $index);

    public function validateData($data, $index): array;
}
