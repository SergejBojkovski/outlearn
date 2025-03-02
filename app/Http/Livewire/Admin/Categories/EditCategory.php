<?php

namespace App\Http\Livewire\Admin\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use LivewireUI\Modal\ModalComponent;

class EditCategory extends ModalComponent
{
    public $category;
    public $categoryId;
    public $name;
    public $description;
    public $slug;
    public $generateSlug = false;

    protected function rules()
    {
        return [
            'name' => 'required|min:3|max:255|unique:categories,name,' . $this->categoryId,
            'description' => 'nullable|max:1000',
            'slug' => 'required|alpha_dash|max:255|unique:categories,slug,' . $this->categoryId,
        ];
    }

    public function mount($category)
    {
        $this->categoryId = $category;
        $this->category = Category::findOrFail($category);
        $this->name = $this->category->name;
        $this->description = $this->category->description;
        $this->slug = $this->category->slug;
    }

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

        $this->category->update([
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
        ]);

        $this->closeModal();

        session()->flash('message', 'Category updated successfully.');
        
        $this->emit('refreshParent');
        $this->emit('notify-saved');
        
        return redirect()->route('admin.categories.index');
    }

    public function render()
    {
        return view('livewire.admin.categories.edit-category');
    }
} 