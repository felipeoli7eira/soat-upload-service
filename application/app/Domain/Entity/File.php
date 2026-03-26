<?php

namespace App\Domain\Entity;

final class File
{
    public function __construct(
        public readonly int|null $id = null,
        public readonly string|null $uuid = null,

        public readonly Protocol $protocol,
        public readonly string $originalName,
        public readonly string $uniqueName,
        public readonly string $mimeType,
        public readonly int $size,
        public readonly string $storageUrl
    ) {}

    public function getAttributes(): array
    {
        return [
            "protocol_uuid" => $this->protocol->uuid(),
            "original_name" => $this->originalName,
            "unique_name"   => $this->uniqueName,
            "mime_type"     => $this->mimeType,
            "size"          => $this->size
        ];
    }
}
