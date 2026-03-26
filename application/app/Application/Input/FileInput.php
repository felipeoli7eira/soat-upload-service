<?php

namespace App\Application\Input;

final readonly class FileInput
{

    public function __construct(
        public readonly string $path,
        public readonly int $size,
        public readonly string $name,
        public readonly string $type,
        public readonly string $extension,
    ) {}

    public function toArray(): array
    {
        return [
            "path"      => $this->path,
            "size"      => $this->size,
            "name"      => $this->name,
            "type"      => $this->type,
            "extension" => $this->extension,
        ];
    }
}
