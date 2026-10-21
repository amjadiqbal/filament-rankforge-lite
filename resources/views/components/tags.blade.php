@if (! empty($meta['title']))
    <title>{{ $meta['title'] }}</title>
@endif
@if (! empty($meta['description']))
    <meta name="description" content="{{ $meta['description'] }}">
@endif
@if (! empty($meta['robots']))
    <meta name="robots" content="{{ implode(', ', $meta['robots']) }}">
@endif
@if (! empty($meta['canonical']))
    <link rel="canonical" href="{{ $meta['canonical'] }}">
@endif
@foreach ($meta['hreflang'] ?? [] as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
@endforeach

@if (! empty($meta['ogTitle']))
    <meta property="og:title" content="{{ $meta['ogTitle'] }}">
@endif
@if (! empty($meta['ogDescription']))
    <meta property="og:description" content="{{ $meta['ogDescription'] }}">
@endif
@if (! empty($meta['ogImage']))
    <meta property="og:image" content="{{ $meta['ogImage'] }}">
@endif
@if (! empty($meta['canonical']))
    <meta property="og:url" content="{{ $meta['canonical'] }}">
@endif
<meta property="og:type" content="website">
@if (! empty($meta['siteName']))
    <meta property="og:site_name" content="{{ $meta['siteName'] }}">
@endif

<meta name="twitter:card" content="{{ empty($meta['ogImage']) ? 'summary' : 'summary_large_image' }}">
@if (! empty($meta['twitterSite']))
    <meta name="twitter:site" content="{{ $meta['twitterSite'] }}">
@endif
@if (! empty($meta['ogTitle']))
    <meta name="twitter:title" content="{{ $meta['ogTitle'] }}">
@endif
@if (! empty($meta['ogDescription']))
    <meta name="twitter:description" content="{{ $meta['ogDescription'] }}">
@endif
@if (! empty($meta['ogImage']))
    <meta name="twitter:image" content="{{ $meta['ogImage'] }}">
@endif

