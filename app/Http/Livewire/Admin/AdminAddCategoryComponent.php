<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Category;
use Carbon\Carbon;
use Livewire\WithFileUploads;
class AdminAddCategoryComponent extends Component
{

    use WithFileUploads;
    public $name;
    public $slug;
    public $image;
    public $is_popular=0;


    public function generateSlug(){
        $this->slug = Str::slug($this->name);
    }
    public function updated($fields){
        $this->validateOnly($fields,[
            'name'=>'required',
            'slug'=>'required',
            'image'=>'required'
        ]);
    }
    public function restoreCategory(){
        $this->validate([
            'name'=>'required',
            'slug'=>'required',
            'image'=>'required'
        ]
        );
        $category = new Category();
        $category->name = $this->name;
        $category->slug = $this->slug;

        $ImageName = Carbon::now()->timestamp.'.'.$this->image->extension();
        $this->image->storeAs('categories',$ImageName); 
        $category->image = $ImageName;

        $category->save();
        session()->flash('message','Category has been Created successfully');
    }
    public function render()
    {
        return view('livewire.admin.admin-add-category-component');
    }
}
