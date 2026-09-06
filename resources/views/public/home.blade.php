@extends('layouts.public')

@section('title', 'MORE Hair Studio | Urban Barbershop & Creative Ecosystem Bandung')
@section('meta_description', 'MORE Hair Studio Bandung. Precision Haircut, Chemical Treatment Keratin, Design Perm & Down Perm. Konsultasi 10-Menit Define Session.')

@section('content')
<div class="bg-white">
    <!-- 1. Hero & Brand Inlook with Authentic Studio Interior -->
    <x-public.hero />

    <!-- 2. Brand Profile: Human-Hair Centered Design & Candid Photography -->
    <x-public.brand-profile />

    <!-- 3. Transparent Detailed Price List -->
    <x-public.official-price-list :services="$services" />

    <!-- 4. Hair Artists Collective -->
    <x-public.stylist-section :stylists="$stylists" />

    <!-- 5. Dynamic Studio Locations (Schedules & details per outlet from database) -->
    <x-public.outlet-section :outlets="$outlets" />

    <!-- 6. 3 Easy Steps to Book Online -->
    <x-public.easy-steps />

    <!-- 7. General Studio Contact & Inquiries -->
    <x-public.studio-info />
</div>
@endsection
