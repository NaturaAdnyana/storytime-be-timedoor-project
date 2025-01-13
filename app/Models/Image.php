<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'path'
    ];

    protected $guarded = [
        'created_at',
        'updated_at',
    ];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
