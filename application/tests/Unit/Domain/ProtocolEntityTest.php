<?php

namespace Tests\Unit\Domain;

use App\Domain\Entity\Protocol;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ProtocolEntityTest extends TestCase
{
    public function test_construtor_define_todas_as_propriedades(): void
    {
        $createdAt = new DateTimeImmutable('2026-04-16 10:00:00');

        $protocol = new Protocol(
            id:        1,
            uuid:      'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            createdAt: $createdAt,
        );

        $this->assertSame(1, $protocol->id);
        $this->assertSame('a1b2c3d4-e5f6-7890-abcd-ef1234567890', $protocol->uuid);
        $this->assertSame($createdAt, $protocol->createdAt);
    }

    public function test_id_pode_ser_nulo(): void
    {
        $protocol = new Protocol(
            id:        null,
            uuid:      'uuid-123',
            createdAt: new DateTimeImmutable(),
        );

        $this->assertNull($protocol->id());
    }

    public function test_metodo_id_retorna_o_valor(): void
    {
        $protocol = new Protocol(
            id:        42,
            uuid:      'uuid-42',
            createdAt: new DateTimeImmutable(),
        );

        $this->assertSame(42, $protocol->id());
    }

    public function test_metodo_uuid_retorna_o_uuid(): void
    {
        $protocol = new Protocol(
            id:        1,
            uuid:      'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            createdAt: new DateTimeImmutable(),
        );

        $this->assertSame('a1b2c3d4-e5f6-7890-abcd-ef1234567890', $protocol->uuid());
    }

    public function test_metodo_created_at_retorna_a_instancia_correta(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01');

        $protocol = new Protocol(
            id:        1,
            uuid:      'uuid-123',
            createdAt: $createdAt,
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $protocol->createdAt());
        $this->assertSame($createdAt, $protocol->createdAt());
    }

    public function test_created_at_preserva_a_data(): void
    {
        $protocol = new Protocol(
            id:        1,
            uuid:      'uuid-123',
            createdAt: new DateTimeImmutable('2026-04-16'),
        );

        $this->assertSame('2026-04-16', $protocol->createdAt()->format('Y-m-d'));
    }

    public function test_uuid_e_uma_string(): void
    {
        $protocol = new Protocol(
            id:        1,
            uuid:      'uuid-abc-123',
            createdAt: new DateTimeImmutable(),
        );

        $this->assertIsString($protocol->uuid());
    }
}
