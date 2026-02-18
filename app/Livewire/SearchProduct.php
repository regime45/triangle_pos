<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;
use Modules\Product\Entities\Product;

class SearchProduct extends Component
{

    public $query;
    public $search_results;
    public $how_many;

    public function mount()
    {
        $this->query = '';
        $this->how_many = 5;
        $this->search_results = Collection::empty();
    }

    public function render()
    {
        return view('livewire.search-product');
    }

    public function updatedQuery()
    {
        if (trim($this->query) === '') {
            $this->search_results = Collection::empty();
            return;
        }

        $this->search_results = Product::where(function ($q) {
            $q->where('product_name', 'like', '%' . $this->query . '%')
                ->orWhere('product_sku', 'like', '%' . $this->query . '%')
                ->orWhere('product_brand', 'like', '%' . $this->query . '%')
                ->orWhere('product_code', 'like', '%' . $this->query . '%')
                ->orWhere('code', 'like', '%' . $this->query . '%')
                ->orWhere('product_location', 'like', '%' . $this->query . '%');
                
        })
            ->limit($this->how_many)
            ->get();
    }

    public function loadMore()
    {
        $this->how_many += 5;
        $this->updatedQuery();
    }

    public function resetQuery()
    {
        $this->query = '';
        $this->how_many = 5;
        $this->search_results = Collection::empty();
    }

    public function selectProduct($product)
    {
        $this->dispatch('productSelected', $product);
    }
}
