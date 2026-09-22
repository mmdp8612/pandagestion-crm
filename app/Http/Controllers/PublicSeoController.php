<?php

namespace App\Http\Controllers;

use App\Support\PublicUrl;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PublicSeoController extends Controller
{
    public function sitemap(): Response
    {
        $properties = DB::table('bienesraices')
            ->select(['Slug', 'updated_at'])
            ->where('Hab', true)
            ->orderBy('id')
            ->get()
            ->map(fn (object $property): array => [
                'url' => PublicUrl::route('public.properties.show', ['slug' => $property->Slug]),
                'lastModified' => $property->updated_at
                    ? CarbonImmutable::parse($property->updated_at)->toAtomString()
                    : null,
            ]);

        return response()
            ->view('public.seo.sitemap', [
                'homeUrl' => PublicUrl::route('home'),
                'catalogUrl' => PublicUrl::route('public.properties.index'),
                'properties' => $properties,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Sitemap: '.PublicUrl::route('public.sitemap'),
            '',
        ]);

        return response($content)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
