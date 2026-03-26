<?php

namespace App\Application\Gateway;

use App\Domain\Interface\ProtocolGatewayInterface;
use App\Domain\Interface\RepositoryInterface;

final class ProtocolGateway implements ProtocolGatewayInterface
{
    public function __construct(public readonly RepositoryInterface $repository) {}

    public function createProtocol(): array
    {
        return $this->repository->createProtocol();
    }

    public function findProtocol(string $uuid): ?array
    {
        return $this->repository->findProtocol($uuid);
    }
}
