@extends('layouts.public')

@section('content')
<div>
    <x-public.hero />
    <x-public.why-more />
    <x-public.signature-ritual />
    <x-public.service-section :services="$services" />
    <x-public.stylist-section :stylists="$stylists" />
    <x-public.review-section :reviews="$reviews" />
    <x-public.about-section />
    <x-public.outlet-section :outlets="$outlets" />
    <x-public.contact-section />
    <x-public.review-cta />
</div>
@endsection
