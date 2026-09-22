<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class PublicUrl
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function route(string $name, array $parameters = []): string
    {
        return self::absolute(route($name, $parameters, false));
    }

    public static function storage(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        return self::absolute(Storage::disk('public')->url($path));
    }

    private static function absolute(string $url): string
    {
        if (preg_match('/^https?:\/\//i', $url) === 1) {
            return $url;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($url, '/');
    }
}
