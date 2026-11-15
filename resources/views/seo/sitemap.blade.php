{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($staticPages as $page)
    <url>
        <loc>{{ $page['loc'] }}</loc>
        <lastmod>{{ $page['lastmod'] }}</lastmod>
        <changefreq>{{ $page['changefreq'] }}</changefreq>
        <priority>{{ $page['priority'] }}</priority>
    </url>
@endforeach
@foreach ($posts as $post)
    <url>
        <loc>{{ $siteUrl }}/gta/{{ $post->slug }}</loc>
        <lastmod>{{ ($post->updated_at ?? $post->published_at ?? now())->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.85</priority>
    </url>
@endforeach
@foreach ($categories as $category)
    <url>
        <loc>{{ $siteUrl }}/gta/category/{{ $category->slug }}</loc>
        <lastmod>{{ $archiveLastmod }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
@endforeach
@foreach ($tags as $tag)
    <url>
        <loc>{{ $siteUrl }}/gta/tag/{{ $tag->slug }}</loc>
        <lastmod>{{ $archiveLastmod }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
@endforeach
</urlset>
