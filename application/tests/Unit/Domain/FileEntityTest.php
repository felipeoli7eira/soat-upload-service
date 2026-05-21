<?php

namespace Tests\Unit\Domain;

use App\Domain\Entity\File;
use App\Domain\Entity\Protocol;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class FileEntityTest extends TestCase
{
    private Protocol $protocol;

    protected function setUp(): void
    {
        $this->protocol = new Protocol(
            id:        1,
            uuid:      'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            createdAt: new DateTimeImmutable(),
        );
    }

    public function test_construtor_define_todas_as_propriedades(): void
    {
        $file = new File(
            id:           1,
            uuid:         'file-uuid-123',
            protocol:     $this->protocol,
            originalName: 'diagram.jpg',
            uniqueName:   'unique-name.jpg',
            mimeType:     'image/jpeg',
            size:         2048,
            storageUrl:   'http://storage.example.com/bucket/unique-name.jpg',
        );

        $this->assertSame(1, $file->id);
        $this->assertSame('file-uuid-123', $file->uuid);
        $this->assertSame($this->protocol, $file->protocol);
        $this->assertSame('diagram.jpg', $file->originalName);
        $this->assertSame('unique-name.jpg', $file->uniqueName);
        $this->assertSame('image/jpeg', $file->mimeType);
        $this->assertSame(2048, $file->size);
        $this->assertSame('http://storage.example.com/bucket/unique-name.jpg', $file->storageUrl);
    }

    public function test_id_pode_ser_nulo(): void
    {
        $file = new File(
            id:           null,
            uuid:         'file-uuid',
            protocol:     $this->protocol,
            originalName: 'diagram.jpg',
            uniqueName:   'unique.jpg',
            mimeType:     'image/jpeg',
            size:         1024,
            storageUrl:   'http://storage.example.com/unique.jpg',
        );

        $this->assertNull($file->id);
    }

    public function test_uuid_pode_ser_nulo(): void
    {
        $file = new File(
            id:           1,
            uuid:         null,
            protocol:     $this->protocol,
            originalName: 'diagram.jpg',
            uniqueName:   'unique.jpg',
            mimeType:     'image/jpeg',
            size:         1024,
            storageUrl:   'http://storage.example.com/unique.jpg',
        );

        $this->assertNull($file->uuid);
    }

    public function test_get_attributes_retorna_array_com_chaves_corretas(): void
    {
        $file = new File(
            id:           1,
            uuid:         'file-uuid',
            protocol:     $this->protocol,
            originalName: 'diagram.jpg',
            uniqueName:   'unique-name.jpg',
            mimeType:     'image/jpeg',
            size:         2048,
            storageUrl:   'http://storage.example.com/unique-name.jpg',
        );

        $attributes = $file->getAttributes();

        $this->assertArrayHasKey('protocol_uuid', $attributes);
        $this->assertArrayHasKey('original_name', $attributes);
        $this->assertArrayHasKey('unique_name', $attributes);
        $this->assertArrayHasKey('mime_type', $attributes);
        $this->assertArrayHasKey('size', $attributes);
    }

    public function test_get_attributes_retorna_valores_corretos(): void
    {
        $file = new File(
            id:           1,
            uuid:         'file-uuid',
            protocol:     $this->protocol,
            originalName: 'diagram.jpg',
            uniqueName:   'unique-name.jpg',
            mimeType:     'image/jpeg',
            size:         2048,
            storageUrl:   'http://storage.example.com/unique-name.jpg',
        );

        $attributes = $file->getAttributes();

        $this->assertSame('a1b2c3d4-e5f6-7890-abcd-ef1234567890', $attributes['protocol_uuid']);
        $this->assertSame('diagram.jpg', $attributes['original_name']);
        $this->assertSame('unique-name.jpg', $attributes['unique_name']);
        $this->assertSame('image/jpeg', $attributes['mime_type']);
        $this->assertSame(2048, $attributes['size']);
    }

    public function test_get_attributes_nao_inclui_campos_internos(): void
    {
        $file = new File(
            id:           1,
            uuid:         'file-uuid',
            protocol:     $this->protocol,
            originalName: 'diagram.pdf',
            uniqueName:   'unique.pdf',
            mimeType:     'application/pdf',
            size:         5000,
            storageUrl:   'http://storage.example.com/unique.pdf',
        );

        $attributes = $file->getAttributes();

        $this->assertArrayNotHasKey('storage_url', $attributes);
        $this->assertArrayNotHasKey('id', $attributes);
        $this->assertArrayNotHasKey('uuid', $attributes);
    }

    public function test_protocol_e_acessivel_pela_propriedade(): void
    {
        $file = new File(
            id:           1,
            uuid:         'file-uuid',
            protocol:     $this->protocol,
            originalName: 'diagram.jpg',
            uniqueName:   'unique.jpg',
            mimeType:     'image/jpeg',
            size:         1024,
            storageUrl:   'http://storage.example.com/unique.jpg',
        );

        $this->assertInstanceOf(Protocol::class, $file->protocol);
        $this->assertSame('a1b2c3d4-e5f6-7890-abcd-ef1234567890', $file->protocol->uuid());
    }
}
