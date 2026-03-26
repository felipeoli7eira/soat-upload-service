<?php

namespace App\Application\Controller;

use App\Application\Gateway\FileGateway;
use App\Application\Gateway\ProtocolGateway;
use App\Application\Input\FileInput;
use App\Application\UseCase\CreateProtocolUseCase;
use App\Application\UseCase\UploadFileUseCase;
use App\Domain\Exception\DomainHttpException;
use App\Infrastructure\FileStorage\MinioFileStorage;
use App\Domain\Interface\RepositoryInterface;

class UploadController
{
    public function __construct(public readonly RepositoryInterface $repository) {}

    public function upload(FileInput $fileInput)
    {
        $protocolGateway = new ProtocolGateway($this->repository);
        $fileGateway = new FileGateway($this->repository);

        $createProtocolUseCase = new CreateProtocolUseCase();
        $createProtocolUseCase->useGateway($protocolGateway);

        $protocol = $createProtocolUseCase->execute();

        $upload = new UploadFileUseCase();
        $upload->defineFile($fileInput);
        $upload->defineStorage(new MinioFileStorage());

        $upload->withProtocol($protocol["uuid"], $protocolGateway);
        $upload->useFileGateway($fileGateway);

        return $upload->execute();
    }
}
