<?php

namespace App\Application\UseCase;

use App\Domain\Exception\DomainHttpException;
use App\Domain\Interface\ProtocolGatewayInterface;

final class FindProtocolUseCase
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
    public function execute(string $protocolUuid): array
    {
        $protocol = $this->gateway->findProtocol($protocolUuid);

        if (!array_key_exists("uuid", $protocol)) {
            throw new DomainHttpException("Protocolo informado não encontrado ({$protocolUuid}).", 400);
        }

        return $protocol;
    }
}
