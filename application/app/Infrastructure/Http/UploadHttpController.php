<?php

namespace App\Infrastructure\Http;

use App\Application\Controller\UploadController;
use Throwable;
use Illuminate\Http\Request;
use App\Application\Input\FileInput;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Response as FacadesResponse;
use Illuminate\Support\Facades\Validator;
use App\Domain\Exception\DomainHttpException;
use App\Infrastructure\Queue\RabbitMQ;
use App\Infrastructure\Repository\PostgresLaravelEloquentRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class UploadHttpController extends BaseHttpController
{
    public function __construct(public readonly RabbitMQ $messageBroker) {}

    public function upload(Request $request)
    {
        try {
            // validacoes basicas sem regra de negocio

            $validated = Validator::make(
                $request->only(["diagram"]),
                [
                    "diagram" => [
                        "required",
                        "file",
                        File::types(["jpg", "jpeg", "png", "pdf"])->max("2mb"), // Size is in kilobytes, so 2048 KB for 2MB
                    ],
                ],
                [
                    "diagram.required" => "É obrigatório um arquivo (.jpg|.jpeg|.png ou pdf) de diagrama para análise.",
                    "diagram.file"     => "Arquivo inválido.",
                    "diagram.max"      => "Arquivo muito grande.",
                    "diagram.types"    => "Arquivo inválido. Envie um arquivo JPG, JPEG, PNG ou PDF.",
                ],
                [],
            );

            if ($validated->fails()) {
                throw new DomainHttpException(
                    $validated->errors()->first(),
                    Response::HTTP_BAD_REQUEST,
                );
            }

            $file = $validated->validated()["diagram"];

            $response = null;

            DB::transaction(function () use (&$file, &$response) {
                $uploadController = new UploadController(new PostgresLaravelEloquentRepository());

                $response = $uploadController->upload(new FileInput(
                    $file->getRealPath(),
                    $file->getSize(),
                    $file->getClientOriginalName(),
                    $file->getClientMimeType(),
                    $file->extension()
                ));
            });

            if (is_array($response)) {
                $this->messageBroker->publishUpload([
                    "protocol"           => $response["protocol_uuid"],
                    "file_original_name" => $response["original_name"],
                    "file_unique_name"   => $response["unique_name"],
                    "file_mime_type"     => $response["mime_type"],
                    "file_size"          => $response["size"],
                    "storage_endpoint"   => $response["endpoint"],
                ]);
            }
        } catch (DomainHttpException $err) {
            return FacadesResponse::json(
                $this->error(
                    [
                        "getFile" => $err->getFile(),
                        "getLine" => $err->getLine(),
                    ],
                    $err->getMessage(),
                ),
                $err->getCode(),
            );
        } catch (Throwable $err) {
            return FacadesResponse::json(
                $this->error(
                    [
                        "getFile" => $err->getFile(),
                        "getLine" => $err->getLine(),
                    ],
                    $err->getMessage(),
                ),
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        if (is_null($response)) {
            return FacadesResponse::json(
                $this->error([], "Erro ao criar um fazer salvar o arquivo e gerar um protocolo."),
            );
        }

        return FacadesResponse::json(
            $this->success([
                "protocol"           => $response["protocol_uuid"],
                "file_original_name" => $response["original_name"],
                "file_unique_name"   => $response["unique_name"],
                "file_mime_type"     => $response["mime_type"],
                "file_size"          => $response["size"],
                "storage_endpoint"   => $response["endpoint"],
            ], "Arquivo recebido com sucesso"),
        );
    }
}
