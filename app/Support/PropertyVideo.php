<?php

namespace App\Support;

final class PropertyVideo
{
    /**
     * @return array{provider: string, label: string, id: string, url: string, embed: string}|null
     */
    public static function parse(?string $url): ?array
    {
        $url = trim((string) $url);

        if ($url === '' || strlen($url) > 500) {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts)
            || ! isset($parts['scheme'], $parts['host'])
            || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            || isset($parts['user'])
            || isset($parts['pass'])) {
            return null;
        }

        $host = strtolower(rtrim($parts['host'], '.'));

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            return self::youtube($host, $parts);
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            return self::vimeo($host, $parts);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $parts
     * @return array{provider: string, label: string, id: string, url: string, embed: string}|null
     */
    private static function youtube(string $host, array $parts): ?array
    {
        $segments = self::pathSegments($parts);
        $id = null;

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $id = $segments[0] ?? null;
        } elseif (($segments[0] ?? null) === 'watch') {
            parse_str((string) ($parts['query'] ?? ''), $query);
            $id = is_string($query['v'] ?? null) ? $query['v'] : null;
        } elseif (in_array($segments[0] ?? null, ['embed', 'shorts', 'live'], true)) {
            $id = $segments[1] ?? null;
        }

        if (! is_string($id) || preg_match('/\A[A-Za-z0-9_-]{11}\z/D', $id) !== 1) {
            return null;
        }

        return [
            'provider' => 'youtube',
            'label' => 'YouTube',
            'id' => $id,
            'url' => 'https://www.youtube.com/watch?v='.$id,
            'embed' => 'https://www.youtube-nocookie.com/embed/'.$id,
        ];
    }

    /**
     * @param  array<string, mixed>  $parts
     * @return array{provider: string, label: string, id: string, url: string, embed: string}|null
     */
    private static function vimeo(string $host, array $parts): ?array
    {
        $segments = self::pathSegments($parts);
        $idIndex = null;

        if ($host === 'player.vimeo.com') {
            if (($segments[0] ?? null) !== 'video' || ! ctype_digit($segments[1] ?? '')) {
                return null;
            }

            $idIndex = 1;
        } else {
            foreach ($segments as $index => $segment) {
                if (ctype_digit($segment)) {
                    $idIndex = $index;
                }
            }
        }

        if ($idIndex === null) {
            return null;
        }

        $id = $segments[$idIndex];
        parse_str((string) ($parts['query'] ?? ''), $query);
        $queryHash = is_string($query['h'] ?? null) ? $query['h'] : null;
        $pathHash = $segments[$idIndex + 1] ?? null;
        $hash = $queryHash ?: $pathHash;

        if ($hash !== null && preg_match('/\A[A-Za-z0-9]+\z/D', $hash) !== 1) {
            return null;
        }

        $hashPath = $hash ? '/'.$hash : '';
        $hashQuery = $hash ? '?h='.rawurlencode($hash) : '';

        return [
            'provider' => 'vimeo',
            'label' => 'Vimeo',
            'id' => $id,
            'url' => 'https://vimeo.com/'.$id.$hashPath,
            'embed' => 'https://player.vimeo.com/video/'.$id.$hashQuery,
        ];
    }

    /**
     * @param  array<string, mixed>  $parts
     * @return array<int, string>
     */
    private static function pathSegments(array $parts): array
    {
        return array_values(array_filter(
            explode('/', trim(rawurldecode((string) ($parts['path'] ?? '')), '/')),
            static fn (string $segment): bool => $segment !== '',
        ));
    }
}
