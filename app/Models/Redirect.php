<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'source_path',
    'target_url',
    'status_code',
    'is_active',
    'hits',
    'last_hit_at',
])]
class Redirect extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_active' => 'boolean',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }

    /**
     * Normalize a URL path to a leading-slash format without trailing slashes.
     */
    public static function normalizePath(string $path): string
    {
        $trimmed = trim(parse_url($path, PHP_URL_PATH) ?: $path, '/');

        return $trimmed === '' ? '/' : '/'.$trimmed;
    }
}
