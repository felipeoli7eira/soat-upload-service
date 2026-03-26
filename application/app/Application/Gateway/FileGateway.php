<?php

namespace App\Application\Gateway;

use App\Domain\Interface\FileGatewayInterface;
use App\Domain\Interface\RepositoryInterface;

final class FileGateway implements FileGatewayInterface
{
    public function __construct(public readonly RepositoryInterface $repository) {}

    public function save(array $data): array
    {
        return $this->repository->saveFile($data);
    }
}
