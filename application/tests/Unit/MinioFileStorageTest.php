<?php

namespace Tests\Unit;

use App\Infrastructure\FileStorage\MinioFileStorage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class MinioFileStorageTest extends TestCase
{
    public function test_upload_returns_path_style_endpoint_with_bucket(): void
    {
        config()->set("filesystems.default", "s3");
        config()->set("filesystems.disks.s3.url", "http://localstack-main:4566");
        config()->set("filesystems.disks.s3.bucket", "fase05-ia");

        $path = tempnam(sys_get_temp_dir(), "upload-test-");
        file_put_contents($path, "test");

        Storage::shouldReceive("put")
            ->once()
            ->with(
                "",
                Mockery::type(File::class),
                Mockery::on(fn (array $options): bool => str_ends_with($options["name"] ?? "", ".jpg"))
            )
            ->andReturn("v87YKUHCwRXPkwts2unN4erqAJVt5XR13ZWIZnPn.jpg");

        Storage::shouldReceive("url")->never();

        try {
            $upload = (new MinioFileStorage())->upload([
                "path"      => $path,
                "extension" => "jpg",
            ]);
        } finally {
            @unlink($path);
        }

        $this->assertSame(
            "http://localstack-main:4566/fase05-ia/v87YKUHCwRXPkwts2unN4erqAJVt5XR13ZWIZnPn.jpg",
            $upload["endpoint"]
        );
        $this->assertStringEndsWith(".jpg", $upload["uniqueName"]);
    }
}
