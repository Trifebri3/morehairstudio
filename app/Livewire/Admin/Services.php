<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Service\Models\Service;
use App\Domains\Service\Models\ServiceCategory;
use Illuminate\Support\Str;

class Services extends Component
{
    use WithPagination;

    public $activeTab = 'services'; // 'services' or 'categories'

    // Service state
    public $search = '';
    public $isEditing = false;
    public $serviceId = null;

    // Service Form fields
    public $name = '';
    public $slug = '';
    public $service_category_id = '';
    public $default_price = 0;
    public $default_duration = 30;
    public $is_active = true;
    public $description = '';

    // Category state
    public $searchCategory = '';
    public $isEditingCategory = false;
    public $categoryId = null;

    // Category Form fields
    public $categoryName = '';
    public $categorySlug = '';
    public $categoryDescription = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'searchCategory' => ['except' => ''],
        'activeTab' => ['except' => 'services']
    ];

    public function selectTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSearchCategory()
    {
        $this->resetPage();
    }

    // --- Service CRUD ---

    public function toggleActive($id)
    {
        $service = Service::findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();
        session()->flash('message', "Status aktif layanan {$service->name} berhasil diperbarui.");
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = true;
    }

    public function edit($id)
    {
        $service = Service::findOrFail($id);
        $this->serviceId = $service->id;
        $this->name = $service->name;
        $this->slug = $service->slug;
        $this->service_category_id = $service->service_category_id;
        $this->default_price = (int) $service->default_price;
        $this->default_duration = $service->default_duration;
        $this->is_active = (bool) $service->is_active;
        $this->description = $service->description;
        $this->isEditing = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'slug' => 'required|string|unique:services,slug,' . ($this->serviceId ?? 'NULL'),
            'service_category_id' => 'required|exists:service_categories,id',
            'default_price' => 'required|numeric|min:0',
            'default_duration' => 'required|integer|min:5',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string',
        ];

        $this->validate($rules);

        if ($this->serviceId) {
            $service = Service::findOrFail($this->serviceId);
            $service->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'service_category_id' => $this->service_category_id,
                'default_price' => $this->default_price,
                'default_duration' => $this->default_duration,
                'is_active' => $this->is_active,
                'description' => $this->description,
            ]);
            session()->flash('message', "Layanan {$service->name} berhasil diperbarui.");
        } else {
            $service = Service::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'service_category_id' => $this->service_category_id,
                'default_price' => $this->default_price,
                'default_duration' => $this->default_duration,
                'is_active' => $this->is_active,
                'description' => $this->description,
            ]);
            session()->flash('message', "Layanan {$service->name} berhasil dibuat.");
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
        $this->serviceId = null;
        $this->name = '';
        $this->slug = '';
        $this->service_category_id = '';
        $this->default_price = 0;
        $this->default_duration = 30;
        $this->is_active = true;
        $this->description = '';
    }

    public function updatedName($value)
    {
        if (!$this->serviceId) {
            $this->slug = Str::slug($value);
        }
    }

    // --- Category CRUD ---

    public function editCategory($id)
    {
        $category = ServiceCategory::findOrFail($id);
        $this->categoryId = $category->id;
        $this->categoryName = $category->name;
        $this->categorySlug = $category->slug;
        $this->categoryDescription = $category->description;
        $this->isEditingCategory = true;
    }

    public function createCategory()
    {
        $this->resetCategoryForm();
        $this->isEditingCategory = true;
    }

    public function saveCategory()
    {
        $rules = [
            'categoryName' => 'required|string|min:3|max:255',
            'categorySlug' => 'required|string|unique:service_categories,slug,' . ($this->categoryId ?? 'NULL'),
            'categoryDescription' => 'nullable|string',
        ];

        $this->validate($rules);

        if ($this->categoryId) {
            $category = ServiceCategory::findOrFail($this->categoryId);
            $category->update([
                'name' => $this->categoryName,
                'slug' => $this->categorySlug,
                'description' => $this->categoryDescription,
            ]);
            session()->flash('message', "Kategori {$category->name} berhasil diperbarui.");
        } else {
            $category = ServiceCategory::create([
                'name' => $this->categoryName,
                'slug' => $this->categorySlug,
                'description' => $this->categoryDescription,
            ]);
            session()->flash('message', "Kategori {$category->name} berhasil dibuat.");
        }

        $this->cancelCategory();
    }

    public function deleteCategory($id)
    {
        $category = ServiceCategory::findOrFail($id);
        
        // Prevent deletion if services are attached
        if (Service::where('service_category_id', $category->id)->exists()) {
            session()->flash('error', "Kategori {$category->name} tidak dapat dihapus karena memiliki layanan aktif terikat.");
            return;
        }

        $category->delete();
        session()->flash('message', "Kategori {$category->name} berhasil dihapus.");
    }

    public function cancelCategory()
    {
        $this->resetCategoryForm();
        $this->isEditingCategory = false;
    }

    private function resetCategoryForm()
    {
        $this->categoryId = null;
        $this->categoryName = '';
        $this->categorySlug = '';
        $this->categoryDescription = '';
    }

    public function updatedCategoryName($value)
    {
        if (!$this->categoryId) {
            $this->categorySlug = Str::slug($value);
        }
    }

    public function render()
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        $services = Service::where('name', 'like', '%' . $this->search . '%')
            ->with('category')
            ->paginate(10, ['*'], 'servicesPage');

        $categories = ServiceCategory::all();

        $paginatedCategories = ServiceCategory::where('name', 'like', '%' . $this->searchCategory . '%')
            ->paginate(10, ['*'], 'categoriesPage');

        return view('livewire.admin.services', compact('services', 'categories', 'paginatedCategories'))
            ->layout('layouts.admin');
    }
}
