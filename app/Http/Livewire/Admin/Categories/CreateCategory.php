<?php

namespace App\Http\Livewire\Admin\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use LivewireUI\Modal\ModalComponent;

class CreateCategory extends ModalComponent
{
    public $name;
    public $description;
    public $slug;
    public $generateSlug = true;

    protected $rules = [
        'name' => 'required|min:3|max:255|unique:categories,name',
        'description' => 'nullable|max:1000',
        'slug' => 'required|alpha_dash|max:255|unique:categories,slug',
    ];

    public function updatedName()
    {
        if ($this->generateSlug) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function updatedSlug()
    {
        $this->generateSlug = false;
    }

    public function save()
    {
        $this->validate();

        Category::create([
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
        ]);

        $this->closeModal();

        session()->flash('message', 'Category created successfully.');
        
        $this->emit('refreshParent');
        $this->emit('notify-saved');
        
        return redirect()->route('admin.categories.index');
    }

    public function render()
    {
        return view('livewire.admin.categories.create-category');
    }
} 