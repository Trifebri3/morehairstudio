@php
    $customSeo = $seoOverrides ?? [];
    // Allow blade sections to override if provided
    if (View::hasSection('title')) {
        $customSeo['title'] = trim(View::getSection('title'));
    }
    if (View::hasSection('meta_description')) {
        $customSeo['description'] = trim(View::getSection('meta_description'));
    }
    if (View::hasSection('og_image')) {
        $customSeo['image'] = trim(View::getSection('og_image'));
    }
    $seoData = \App\Domains\SEO\Services\SEOService::getMetadata(null, $customSeo);
@endphp

<title>{{ $seoData['title'] }}</title>

<!-- Standard SEO Meta -->
<meta name="description" content="{{ $seoData['description'] }}">
<meta name="keywords" content="{{ $seoData['keywords'] }}">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="author" content="MORE Hair Studio">
<link rel="canonical" href="{{ $seoData['canonical'] }}">

<!-- Bandung Local Geo Meta Tags -->
<meta name="geo.region" content="{{ $seoData['geo']['region'] }}">
<meta name="geo.placename" content="{{ $seoData['geo']['placename'] }}">
<meta name="geo.position" content="{{ $seoData['geo']['position'] }}">
<meta name="ICBM" content="{{ $seoData['geo']['icbm'] }}">

<!-- Open Graph / Facebook / WhatsApp Share Meta -->
<meta property="og:site_name" content="{{ $seoData['site_name'] }}">
<meta property="og:type" content="{{ $seoData['type'] }}">
<meta property="og:url" content="{{ $seoData['url'] }}">
<meta property="og:title" content="{{ $seoData['title'] }}">
<meta property="og:description" content="{{ $seoData['description'] }}">
<meta property="og:image" content="{{ $seoData['image'] }}">
<meta property="og:image:secure_url" content="{{ $seoData['image'] }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $seoData['title'] }}">
<meta property="og:locale" content="{{ $seoData['locale'] }}">

<!-- Twitter Card Meta -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@morehairstudio">
<meta name="twitter:title" content="{{ $seoData['title'] }}">
<meta name="twitter:description" content="{{ $seoData['description'] }}">
<meta name="twitter:image" content="{{ $seoData['image'] }}">

<!-- Icons & Theme -->
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192x192.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logokotak.png') }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<meta name="theme-color" content="#121110">

<!-- JSON-LD Structured Data Schema for Google LocalBusiness & Rich Search Results -->
<script type="application/ld+json">
{!! json_encode($seoData['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
