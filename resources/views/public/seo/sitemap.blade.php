{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ $homeUrl }}</loc>
    </url>
    <url>
        <loc>{{ $catalogUrl }}</loc>
    </url>
@foreach ($properties as $property)
    <url>
        <loc>{{ $property['url'] }}</loc>
@if ($property['lastModified'])
        <lastmod>{{ $property['lastModified'] }}</lastmod>
@endif
    </url>
@endforeach
</urlset>
