<?php

namespace App\Domain\Interface;

interface FileStorage
{
    public function upload(array $data, null|array $settings = null): array;
}
