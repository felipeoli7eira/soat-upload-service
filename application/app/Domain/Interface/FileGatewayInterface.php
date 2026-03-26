<?php

namespace App\Domain\Interface;

interface FileGatewayInterface
{
    public function save(array $data): array;
}
