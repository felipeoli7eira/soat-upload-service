<?php

namespace App\Infrastructure\Repository;

use App\Domain\Interface\RepositoryInterface;
use App\Infrastructure\ModelRepository\File;
use App\Infrastructure\ModelRepository\Protocol;
use Illuminate\Support\Str;

final class PostgresLaravelEloquentRepository implements RepositoryInterface
{
    public function createProtocol(): array
    {
        $model = Protocol::create([
            "uuid" => Str::uuid()->toString()
        ]);

        return $model->toArray();
    }

    public function findProtocol(string $uuid): ?array
    {
        return Protocol::where("uuid", $uuid)->firstOrFail()->toArray(["*"]);
    }

    public function saveFile(array $data): array
    {
        $save = File::create([
            "uuid"          => Str::uuid()->toString(),
            "protocol_uuid" => $data["protocol_uuid"],
            "original_name" => $data["original_name"],
            "unique_name"   => $data["unique_name"],
            "mime_type"     => $data["mime_type"],
            "size"          => $data["size"],
            "storage_url"   => $data["protocol_uuid"],
        ]);

        return $save->toArray();
    }
}
