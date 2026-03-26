<?php

namespace App\Infrastructure\ModelRepository;

use Illuminate\Database\Eloquent\Model;

class Protocol extends Model
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public $table = "protocols";

    public $fillable = ["uuid", "created_at"];
}
