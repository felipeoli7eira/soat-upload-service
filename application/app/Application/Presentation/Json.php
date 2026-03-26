<?php

namespace App\Presentation;

use App\Domain\Interface\Presentation;

final class Json implements Presentation
{
    public static function present(array $data, null|array $settings = null): mixed
    {
        return json_encode($data);
    }
}
