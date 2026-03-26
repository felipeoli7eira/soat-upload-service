<?php

namespace App\Domain\Interface;

interface Presentation
{
    public static function present(array $data, null|array $settings = null): mixed;
}
