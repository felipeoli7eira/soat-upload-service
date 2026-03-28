<?php

namespace App\Application\UseCase;

use App\Application\Input\FileInput;
use App\Domain\Entity\File;
use App\Domain\Entity\Protocol;
use App\Domain\Exception\DomainHttpException;
use App\Domain\Interface\FileGatewayInterface;
use App\Domain\Interface\FileStorage;
use App\Domain\Interface\ProtocolGatewayInterface;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;

class UploadFileUseCase
{
    public readonly FileStorage $fileStorage;
    public readonly FileInput $fileInput;

    public readonly ProtocolGatewayInterface $protocolGateway;
    public readonly FileGatewayInterface $fileGateway;

    public readonly string $protocol;

    public function defineStorage(FileStorage $fileStorage): self
    {
        $this->fileStorage = $fileStorage;

        return $this;
    }

    public function defineFile(FileInput $fileInput): self
    {
        $this->fileInput = $fileInput;

        return $this;
    }

    public function withProtocol(string $protocol, ProtocolGatewayInterface $protocolGateway): self
    {
        $this->protocol = $protocol;
        $this->protocolGateway = $protocolGateway;

        return $this;
    }

    public function useFileGateway(FileGatewayInterface $fileGateway): self
    {
        $this->fileGateway = $fileGateway;

        return $this;
    }

    public function execute(): array
    {
        $findProtocolUseCase = new FindProtocolUseCase();
        $findProtocolUseCase->useGateway($this->protocolGateway);

        $protocol = $findProtocolUseCase->execute($this->protocol);

        if (! is_array($protocol) || (is_array($protocol) && !isset($protocol["uuid"]))) {
            throw new DomainException("Protocolo informado não encontrado: {$this->protocol}", 400);
        }

        $protocolEntity = new Protocol(
            $protocol["id"],
            $protocol["uuid"],
            new DateTimeImmutable($protocol["created_at"], new DateTimeZone("America/Sao_Paulo")),
        );

        $upload = $this->fileStorage->upload($this->fileInput->toArray(), $settings = []);

        $fileEntity = new File(
            protocol: $protocolEntity,
            originalName: $this->fileInput->name,
            uniqueName: $upload["uniqueName"],
            mimeType: $this->fileInput->type,
            size: $this->fileInput->size,
            storageUrl: $upload["endpoint"],

            id: null,
            uuid: null
        );

        $file = $this->fileGateway->save([
            ...$fileEntity->getAttributes(),

            "storage_url" => $upload["endpoint"],
        ]);

        if (array_key_exists("id", $file) === false) {
            throw new DomainHttpException("Erro ao salvar o upload", 500);
        }

        return [...$file, ...$upload];
    }
}
