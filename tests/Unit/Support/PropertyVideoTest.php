<?php

namespace Tests\Unit\Support;

use App\Support\PropertyVideo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PropertyVideoTest extends TestCase
{
    /**
     * @param  array{provider: string, label: string, id: string, url: string, embed: string}  $expected
     */
    #[DataProvider('supportedUrls')]
    public function test_it_normalizes_supported_video_urls(string $url, array $expected): void
    {
        $this->assertSame($expected, PropertyVideo::parse($url));
    }

    /**
     * @return array<string, array{string, array{provider: string, label: string, id: string, url: string, embed: string}}>
     */
    public static function supportedUrls(): array
    {
        $youtube = [
            'provider' => 'youtube',
            'label' => 'YouTube',
            'id' => 'dQw4w9WgXcQ',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'embed' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
        ];
        $vimeo = [
            'provider' => 'vimeo',
            'label' => 'Vimeo',
            'id' => '76979871',
            'url' => 'https://vimeo.com/76979871/8272103f6e',
            'embed' => 'https://player.vimeo.com/video/76979871?h=8272103f6e',
        ];

        return [
            'YouTube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30', $youtube],
            'YouTube short link' => ['https://youtu.be/dQw4w9WgXcQ?si=example', $youtube],
            'YouTube Shorts' => ['https://youtube.com/shorts/dQw4w9WgXcQ', $youtube],
            'YouTube embed' => ['https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $youtube],
            'Vimeo unlisted' => ['https://vimeo.com/76979871/8272103f6e', $vimeo],
            'Vimeo player' => ['https://player.vimeo.com/video/76979871?h=8272103f6e', $vimeo],
        ];
    }

    #[DataProvider('unsupportedUrls')]
    public function test_it_rejects_unsupported_or_deceptive_urls(?string $url): void
    {
        $this->assertNull(PropertyVideo::parse($url));
    }

    /**
     * @return array<string, array{string|null}>
     */
    public static function unsupportedUrls(): array
    {
        return [
            'empty' => [null],
            'unsupported provider' => ['https://example.com/video/123'],
            'deceptive YouTube domain' => ['https://youtube.com.example.test/watch?v=dQw4w9WgXcQ'],
            'invalid YouTube ID' => ['https://www.youtube.com/watch?v=short'],
            'invalid Vimeo ID' => ['https://vimeo.com/not-a-video'],
            'embedded credentials' => ['https://user:password@youtube.com/watch?v=dQw4w9WgXcQ'],
            'unsafe scheme' => ['javascript:alert(1)'],
        ];
    }
}
