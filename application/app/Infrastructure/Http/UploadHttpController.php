<?php

namespace App\Infrastructure\Http;

use App\Application\Controller\UploadController;
use Throwable;
use Illuminate\Http\Request;
use App\Application\Input\FileInput;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response as FacadesResponse;
use Illuminate\Support\Facades\Validator;
use App\Domain\Exception\DomainHttpException;
use App\Infrastructure\FileStorage\MinioFileStorage;
use App\Infrastructure\Repository\PostgresLaravelEloquentRepository;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class UploadHttpController extends BaseHttpController
{
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
                    "diagram.required" => "É obrigatório um arquivo (imagem ou pdf) de diagrama para análise.",
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

            $uploadController = new UploadController(new PostgresLaravelEloquentRepository());

            $response = $uploadController->upload(new FileInput(
                $file->getRealPath(),
                $file->getSize(),
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
                $file->extension()
            ));
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

        return FacadesResponse::json(
            $this->success([
                "protocolo" => $response["protocol_uuid"]
            ], "Arquivo recebido com sucesso"),
        );
    }
}
