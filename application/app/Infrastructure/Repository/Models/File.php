<?php

namespace App\Infrastructure\Repository\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    public $table = "files";

    public $fillable = [
        "uuid",
        "protocol_uuid",
        "original_name",
        "unique_name",
        "mime_type",
        "size",
        "storage_url"
    ];

    public $timestamps = false;
}
