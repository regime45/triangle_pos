<?php

namespace App\Livewire\Adjustment;

use Illuminate\Support\Collection;
use Livewire\Component;
use Modules\Product\Entities\Product;

class ProductTable extends Component
{

    protected $listeners = ['productSelected'];

    public $products;
    public $hasAdjustments;

    public function mount($adjustedProducts = null)
    {
        $this->products = [];

        if ($adjustedProducts) {
            $this->hasAdjustments = true;
            $this->products = $adjustedProducts;
        } else {
            $this->hasAdjustments = false;
        }
    }

    public function render()
    {
        return view('livewire.adjustment.product-table');
    }

    public function productSelected($product)
    {

        // Check duplicates
        if ($this->hasAdjustments) {
            if (in_array($product, array_map(function ($adjustment) {
                return $adjustment['product'];
            }, $this->products))) {
                return session()->flash('message', 'Already exists in the product list!');
            }
        } else {
            if (in_array($product, array_map(function ($p) {
                return $p['product'];
            }, $this->products))) {
                return session()->flash('message', 'Already exists in the product list!');
            }
        }

        // ✅ ALWAYS push in SAME STRUCTURE
        $this->products[] = [
            'product' => $product
        ];
    }


    public function removeProduct($key)
    {
        unset($this->products[$key]);
    }
}
