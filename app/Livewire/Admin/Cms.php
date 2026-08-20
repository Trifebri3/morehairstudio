<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Domains\CMS\Services\CmsService;

class Cms extends Component
{
    // Form fields for Hero
    public $hero_tagline_id;
    public $hero_tagline_en;
    public $hero_description_id;
    public $hero_description_en;

    // Form fields for About
    public $about_tagline_id;
    public $about_tagline_en;
    public $about_description_1_id;
    public $about_description_1_en;
    public $about_description_2_id;
    public $about_description_2_en;

    // Why MORE
    public $why_title_id;
    public $why_title_en;
    public $why_subtitle_id;
    public $why_subtitle_en;

    // Payment Gateway Active Toggle
    public $payment_gateway_active;

    public $successMessage = null;

    public function mount()
    {
        $this->loadFields();
    }

    public function loadFields()
    {
        $this->hero_tagline_id = CmsService::get('hero_tagline', 'id');
        $this->hero_tagline_en = CmsService::get('hero_tagline', 'en');
        $this->hero_description_id = CmsService::get('hero_description', 'id');
        $this->hero_description_en = CmsService::get('hero_description', 'en');

        $this->about_tagline_id = CmsService::get('about_tagline', 'id');
        $this->about_tagline_en = CmsService::get('about_tagline', 'en');
        $this->about_description_1_id = CmsService::get('about_description_1', 'id');
        $this->about_description_1_en = CmsService::get('about_description_1', 'en');
        $this->about_description_2_id = CmsService::get('about_description_2', 'id');
        $this->about_description_2_en = CmsService::get('about_description_2', 'en');

        $this->why_title_id = CmsService::get('why_title', 'id');
        $this->why_title_en = CmsService::get('why_title', 'en');
        $this->why_subtitle_id = CmsService::get('why_subtitle', 'id');
        $this->why_subtitle_en = CmsService::get('why_subtitle', 'en');

        $this->payment_gateway_active = CmsService::get('payment_gateway_active', 'id');
    }

    public function save()
    {
        CmsService::set('hero_tagline', ['id' => $this->hero_tagline_id, 'en' => $this->hero_tagline_en]);
        CmsService::set('hero_description', ['id' => $this->hero_description_id, 'en' => $this->hero_description_en]);

        CmsService::set('about_tagline', ['id' => $this->about_tagline_id, 'en' => $this->about_tagline_en]);
        CmsService::set('about_description_1', ['id' => $this->about_description_1_id, 'en' => $this->about_description_1_en]);
        CmsService::set('about_description_2', ['id' => $this->about_description_2_id, 'en' => $this->about_description_2_en]);

        CmsService::set('why_title', ['id' => $this->why_title_id, 'en' => $this->why_title_en]);
        CmsService::set('why_subtitle', ['id' => $this->why_subtitle_id, 'en' => $this->why_subtitle_en]);

        CmsService::set('payment_gateway_active', ['id' => $this->payment_gateway_active, 'en' => $this->payment_gateway_active]);

        $this->successMessage = 'Konfigurasi public berhasil disimpan!';
    }

    public function render()
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        return view('livewire.admin.cms')->layout('layouts.admin');
    }
}
