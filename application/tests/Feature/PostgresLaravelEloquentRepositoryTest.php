<?php

namespace Tests\Feature;

use App\Infrastructure\Repository\PostgresLaravelEloquentRepository;
use Tests\TestCase;

class PostgresLaravelEloquentRepositoryTest extends TestCase
{
    public function test_save_file_persists_storage_url_from_payload(): void
    {
        $repository = new PostgresLaravelEloquentRepository();
        $storageUrl = "http://localstack-main:4566/fase05-ia/v87YKUHCwRXPkwts2unN4erqAJVt5XR13ZWIZnPn.jpg";

        $file = $repository->saveFile([
            "protocol_uuid" => "550e8400-e29b-41d4-a716-446655440000",
            "original_name" => "diagram.jpg",
            "unique_name"   => "v87YKUHCwRXPkwts2unN4erqAJVt5XR13ZWIZnPn.jpg",
            "mime_type"     => "image/jpeg",
            "size"          => 2048,
            "storage_url"   => $storageUrl,
        ]);

        $this->assertSame($storageUrl, $file["storage_url"]);
        $this->assertDatabaseHas("files", [
            "protocol_uuid" => "550e8400-e29b-41d4-a716-446655440000",
            "storage_url"   => $storageUrl,
        ]);
    }
}
