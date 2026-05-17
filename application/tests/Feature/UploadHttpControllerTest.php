<?php

namespace Tests\Feature;

use App\Infrastructure\Http\UploadHttpController;
use App\Infrastructure\Queue\RabbitMQ;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UploadHttpControllerTest extends TestCase
{
    public function test_upload_response_and_queue_payload_use_bucket_in_storage_endpoint(): void
    {
        config()->set("filesystems.default", "s3");
        config()->set("filesystems.disks.s3.url", "http://localstack-main:4566");
        config()->set("filesystems.disks.s3.bucket", "fase05-ia");

        Storage::shouldReceive("put")
            ->once()
            ->with(
                "",
                Mockery::type(File::class),
                Mockery::on(fn (array $options): bool => str_ends_with($options["name"] ?? "", ".jpg"))
            )
            ->andReturn("v87YKUHCwRXPkwts2unN4erqAJVt5XR13ZWIZnPn.jpg");

        Storage::shouldReceive("url")->never();

        $expectedEndpoint = "http://localstack-main:4566/fase05-ia/v87YKUHCwRXPkwts2unN4erqAJVt5XR13ZWIZnPn.jpg";

        $messageBroker = Mockery::mock(RabbitMQ::class);
        $messageBroker->shouldReceive("publishUpload")
            ->once()
            ->with(Mockery::on(function (array $payload) use ($expectedEndpoint): bool {
                return ($payload["storage_endpoint"] ?? null) === $expectedEndpoint;
            }))
            ->andReturnTrue();

        $request = Request::create("/upload", "POST", [], [], [
            "diagram" => UploadedFile::fake()->create("diagram.jpg", 100, "image/jpeg"),
        ]);

        $response = (new UploadHttpController($messageBroker))->upload($request);
        $payload = $response->getData(true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), json_encode($payload));
        $this->assertSame($expectedEndpoint, $payload["data"]["storage_endpoint"]);
    }
}
