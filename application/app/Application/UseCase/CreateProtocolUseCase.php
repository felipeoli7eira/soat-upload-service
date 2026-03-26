<?php

namespace App\Application\UseCase;

use App\Domain\Exception\DomainHttpException;
use App\Domain\Interface\ProtocolGatewayInterface;

final class CreateProtocolUseCase
{
    public ProtocolGatewayInterface $gateway;

    public function useGateway(ProtocolGatewayInterface $gateway): self
    {
        $this->gateway = $gateway;
        return $this;
    }

    /**
     * @return array
     * @throws DomainHttpException
     */
    public function execute(): array
    {
        $newProtocol = $this->gateway->createProtocol();

        if (!array_key_exists("uuid", $newProtocol)) {
            throw new DomainHttpException("Erro ao abrir um protocolo.", 500);
        }

        return $newProtocol;
    }
}
