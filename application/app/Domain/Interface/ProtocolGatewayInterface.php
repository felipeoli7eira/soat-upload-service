<?php

namespace App\Domain\Interface;

interface ProtocolGatewayInterface
{
    public function createProtocol(): array;
    public function findProtocol(string $uuid): array|null;
}
