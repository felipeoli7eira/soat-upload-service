<?php

namespace App\Infrastructure\FileStorage;

use App\Domain\Exception\DomainHttpException;
use App\Domain\Interface\FileStorage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MinioFileStorage implements FileStorage
{
    public function upload(array $data, ?array $settings = null): array
    {
        $options = [
            "name" => Str::uuid()->toString() . "." . $data["extension"]
        ];

        $uploadName = Storage::put("", new File($data["path"]), $options);

        if (is_string($uploadName) === false) {
            throw new DomainHttpException("Erro ao fazer upload do arquivo. O storage está fora do ar? o Bucket está configurado corretamente?", 500);
        }

        return [
            "endpoint"   => Storage::url($uploadName),
            "uniqueName" => $options["name"]
        ];
    }
}
