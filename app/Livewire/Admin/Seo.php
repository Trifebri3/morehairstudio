<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\SEO\Models\SEOMetadata;

class Seo extends Component
{
    use WithPagination;

    public $search = '';
    public $isEditing = false;
    public $seoId = null;

    // Form fields
    public $path = '';
    public $meta_title = '';
    public $meta_description = '';
    public $canonical_url = '';
    public $og_title = '';
    public $og_description = '';
    public $og_image = '';
    public $schema = '';

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $seo = SEOMetadata::findOrFail($id);
        $seo->delete();
        session()->flash('message', "Data SEO untuk path {$seo->path} berhasil dihapus.");
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = true;
    }

    public function edit($id)
    {
        $seo = SEOMetadata::findOrFail($id);
        $this->seoId = $seo->id;
        $this->path = $seo->path;
        $this->meta_title = $seo->meta_title;
        $this->meta_description = $seo->meta_description;
        $this->canonical_url = $seo->canonical_url;
        $this->og_title = $seo->og_title;
        $this->og_description = $seo->og_description;
        $this->og_image = $seo->og_image;
        $this->schema = $seo->schema;
        $this->isEditing = true;
    }

    public function save()
    {
        $rules = [
            'path' => 'required|string|min:1|max:255|unique:seo_metadata,path,' . ($this->seoId ?? 'NULL'),
            'meta_title' => 'required|string|min:3|max:100',
            'meta_description' => 'required|string|min:5|max:255',
            'canonical_url' => 'nullable|url|max:255',
            'og_title' => 'nullable|string|max:100',
            'og_description' => 'nullable|string|max:255',
            'og_image' => 'nullable|string|max:255',
            'schema' => 'nullable|string',
        ];

        $this->validate($rules);

        if ($this->seoId) {
            $seo = SEOMetadata::findOrFail($this->seoId);
            $seo->update([
                'path' => $this->path,
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'canonical_url' => $this->canonical_url ?: null,
                'og_title' => $this->og_title ?: null,
                'og_description' => $this->og_description ?: null,
                'og_image' => $this->og_image ?: null,
                'schema' => $this->schema ?: null,
            ]);
            session()->flash('message', "SEO Metadata untuk path {$seo->path} berhasil diperbarui.");
        } else {
            $seo = SEOMetadata::create([
                'path' => $this->path,
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'canonical_url' => $this->canonical_url ?: null,
                'og_title' => $this->og_title ?: null,
                'og_description' => $this->og_description ?: null,
                'og_image' => $this->og_image ?: null,
                'schema' => $this->schema ?: null,
            ]);
            session()->flash('message', "SEO Metadata untuk path {$seo->path} berhasil dibuat.");
        }

        $this->cancel();
    }

    public function cancel()
    {
        $this->resetForm();
        $this->isEditing = false;
    }

    private function resetForm()
    {
        $this->seoId = null;
        $this->path = '';
        $this->meta_title = '';
        $this->meta_description = '';
        $this->canonical_url = '';
        $this->og_title = '';
        $this->og_description = '';
        $this->og_image = '';
        $this->schema = '';
    }

    public function render()
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        $seoRecords = SEOMetadata::where('path', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.admin.seo', compact('seoRecords'))->layout('layouts.admin');
    }
}
