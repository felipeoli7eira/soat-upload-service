<?php

namespace App\Domain\Interface;

interface RepositoryInterface
{
    public function createProtocol(): array;
    public function findProtocol(string $uuid): ?array;

    public function saveFile(array $data): array;
}
