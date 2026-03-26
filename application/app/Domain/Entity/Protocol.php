<?php

namespace App\Domain\Entity;

use DateTimeImmutable;

final class Protocol
{
    public function __construct(
        public readonly int|null $id = null,
        public readonly string $uuid,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function id(): int|null
    {
        return $this->id;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function createdAtString(): string
    {
        return $this->createdAt->format("YYYY-MM-DD HH:mm:ss");
    }
}
