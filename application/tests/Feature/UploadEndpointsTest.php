<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadEndpointsTest extends TestCase
{
    public function test_ping_retorna_pong(): void
    {
        $this->getJson('/api/ping')
            ->assertOk()
            ->assertJson(['err' => false, 'msg' => 'pong']);
    }

    public function test_upload_retorna_400_quando_arquivo_nao_enviado(): void
    {
        $this->postJson('/api/upload')
            ->assertStatus(400)
            ->assertJson(['err' => true]);
    }

    public function test_upload_retorna_400_quando_tipo_de_arquivo_invalido(): void
    {
        $this->post('/api/upload', [
            'diagram' => UploadedFile::fake()->create('document.txt', 100, 'text/plain'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertJson(['err' => true]);
    }

    public function test_upload_retorna_400_quando_arquivo_maior_que_2mb(): void
    {
        $this->post('/api/upload', [
            'diagram' => UploadedFile::fake()->create('diagram.jpg', 3000, 'image/jpeg'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertJson(['err' => true]);
    }

    /** @dataProvider tiposInvalidosProvider */
    public function test_upload_rejeita_tipos_nao_permitidos(string $filename, string $mimeType): void
    {
        $this->post('/api/upload', [
            'diagram' => UploadedFile::fake()->create($filename, 100, $mimeType),
        ], ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertJson(['err' => true]);
    }

    public static function tiposInvalidosProvider(): array
    {
        return [
            'gif'  => ['diagram.gif', 'image/gif'],
            'txt'  => ['document.txt', 'text/plain'],
            'docx' => ['document.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'mp4'  => ['video.mp4', 'video/mp4'],
        ];
    }

    public function test_fallback_retorna_404(): void
    {
        $this->getJson('/api/rota-inexistente')
            ->assertNotFound()
            ->assertJson(['err' => true]);
    }
}
