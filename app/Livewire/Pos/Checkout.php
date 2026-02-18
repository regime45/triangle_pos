<?php

namespace App\Livewire\Pos;

use Gloudemans\Shoppingcart\Facades\Cart;
use Livewire\Component;

class Checkout extends Component
{

    public $listeners = ['productSelected', 'discountModalRefresh'];

    public $cart_instance;
    public $customers;
    public $global_discount;
    public $global_charges;


    // ✅ REQUIRED
    public $global_discount_type = 'fixed'; // fixed | percentage
    public $global_charges_type = 'fixed'; 


    public $global_tax;
    public $shipping;
    


    public $quantity;
    public $check_quantity;
    public $discount_type;

    
    public $item_discount;
    public $data;
    public $customer_id;
    public $total_amount;
    public $display_discount = 0; // Store actual discount amount for display
    public $display_charges = 0; // Store actual discount amount for display

    public function mount($cartInstance, $customers)
    {
        $this->cart_instance = $cartInstance;
        $this->customers = $customers;
        $this->global_discount = 0;
        $this->global_tax = 0;
        $this->shipping = 0.00;
        $this->global_charges = 0.00;

        $this->check_quantity = [];
        $this->quantity = [];
        $this->discount_type = [];
        $this->item_discount = [];
        $this->total_amount = 0;
         $this->customer_id = 2;
    }

    // ✅ Computed property for display discount
    public function getDisplayDiscountProperty()
    {
        $subtotal = (float) Cart::instance($this->cart_instance)->subtotal(2, '.', '');

        if ($this->global_discount_type === 'percentage') {
            return round($subtotal * ($this->global_discount / 100), 2);
        }

        return round($this->global_discount, 2); // fixed
    }

    public function getGrandTotalProperty()
    {
        $subtotal = (float) Cart::instance($this->cart_instance)->subtotal(2, '.', '');
        $tax = (float) Cart::instance($this->cart_instance)->tax();
        $shipping = (float) $this->shipping;
        $charges = (float) $this->display_charges;
        $discount = $this->display_discount;

        return round($subtotal - $discount + $tax + $shipping +  $charges, 2);
    }


    public function hydrate()
    {
        $this->total_amount = $this->calculateTotal();
    }

    public function render()
    {
        $cart_items = Cart::instance($this->cart_instance)->content();

        return view('livewire.pos.checkout', [
            'cart_items' => $cart_items,
            'display_discount' => $this->display_discount,
            'display_charges' => $this->display_charges,
            'grand_total' => $this->grand_total,
        ]);
    }

    public function proceed()
    {
        if ($this->customer_id != null) {
            $this->dispatch('showCheckoutModal');
        } else {
            session()->flash('message', 'Please Select Customer!');
        }
    }

    public function calculateTotal()
    {
        return Cart::instance($this->cart_instance)->total() + $this->shipping;
    }

    public function resetCart()
    {
        Cart::instance($this->cart_instance)->destroy();
    }

    public function productSelected($product)
    {
        $cart = Cart::instance($this->cart_instance);

        $exists = $cart->search(function ($cartItem, $rowId) use ($product) {
            return $cartItem->id == $product['id'];
        });

        if ($exists->isNotEmpty()) {
            session()->flash('message', 'Product exists in the cart!');

            return;
        }

        $cart->add([
            'id'      => $product['id'],
            'name'    => $product['product_name'],
            'qty'     => 1,
            'price'   => $this->calculate($product)['price'],
            'weight'  => 1,
            'options' => [
                'product_discount'      => 0.00,
                'product_discount_type' => 'fixed',
                'sub_total'             => $this->calculate($product)['sub_total'],
                'code'                  => $product['product_code'],
                'stock'                 => $product['product_quantity'],
                'unit'                  => $product['product_unit'],
                'product_tax'           => $this->calculate($product)['product_tax'],
                'unit_price'            => $this->calculate($product)['unit_price']
            ]
        ]);

        $this->check_quantity[$product['id']] = $product['product_quantity'];
        $this->quantity[$product['id']] = 1;
        $this->discount_type[$product['id']] = 'fixed';
        $this->item_discount[$product['id']] = 0;
        $this->total_amount = $this->calculateTotal();
    }

    public function removeItem($row_id)
    {
        Cart::instance($this->cart_instance)->remove($row_id);
    }

    public function updatedGlobalTax()
    {
        Cart::instance($this->cart_instance)->setGlobalTax((int)$this->global_tax);
    }



    public function updatedGlobalDiscount()
    {
        $this->applyGlobalDiscount();
    }

    public function updatedGlobalDiscountType()
    {
        $this->applyGlobalDiscount();
    }

    public function updatedGlobalCharges()
{
    $this->applyGlobalDiscount();
}

public function updatedGlobalChargesType()
{
    $this->applyGlobalDiscount();
}

    // Computed discount





  private function applyGlobalDiscount()
{
    $cart = Cart::instance($this->cart_instance);
    $subtotal = (float) $cart->subtotal(2, '.', '');
    $tax = (float) $cart->tax();
    $shipping = (float) $this->shipping;
    //$additional_charges = (float) $this->additional_charges;

    if ($subtotal <= 0) {
        $this->display_discount = 0;
        $this->total_amount = $subtotal + $tax + $shipping;
        return;
    }

    if ($this->global_charges_type === 'percentage') {
        $percentage = min((float)$this->global_charges, 100);
        $this->display_charges = round($subtotal * $percentage / 100); // discount in peso
    } else {
        // Fixed amount
        $this->display_charges = min((float)$this->global_charges, $subtotal); // max discount = subtotal
    }



    if ($this->global_discount_type === 'percentage') {
        $percentage = min((float)$this->global_discount, 100);
        $this->display_discount = round($subtotal * $percentage / 100); // discount in peso
    } else {
        // Fixed amount
        $this->display_discount = min((float)$this->global_discount, $subtotal); // max discount = subtotal
    }



    $this->total_amount = $subtotal - $this->display_discount + $tax + $shipping + $this->display_charges ;
}






    public function updateQuantity($row_id, $product_id)
    {
        if ($this->check_quantity[$product_id] < $this->quantity[$product_id]) {
            session()->flash('message', 'The requested quantity is not available in stock.');

            return;
        }

        Cart::instance($this->cart_instance)->update($row_id, $this->quantity[$product_id]);

        $cart_item = Cart::instance($this->cart_instance)->get($row_id);

        Cart::instance($this->cart_instance)->update($row_id, [
            'options' => [
                'sub_total'             => $cart_item->price * $cart_item->qty,
                'code'                  => $cart_item->options->code,
                'stock'                 => $cart_item->options->stock,
                'unit'                  => $cart_item->options->unit,
                'product_tax'           => $cart_item->options->product_tax,
                'unit_price'            => $cart_item->options->unit_price,
                'product_discount'      => $cart_item->options->product_discount,
                'product_discount_type' => $cart_item->options->product_discount_type,
            ]
        ]);
    }

    public function updatedDiscountType($value, $name)
    {
        $this->item_discount[$name] = 0;
    }

    public function discountModalRefresh($product_id, $row_id)
    {
        $this->updateQuantity($row_id, $product_id);
    }

    public function setProductDiscount($row_id, $product_id)
    {
        $cart_item = Cart::instance($this->cart_instance)->get($row_id);

        if ($this->discount_type[$product_id] == 'fixed') {
            Cart::instance($this->cart_instance)
                ->update($row_id, [
                    'price' => ($cart_item->price + $cart_item->options->product_discount) - $this->item_discount[$product_id]
                ]);

            $discount_amount = $this->item_discount[$product_id];

            $this->updateCartOptions($row_id, $product_id, $cart_item, $discount_amount);
        } elseif ($this->discount_type[$product_id] == 'percentage') {
            $discount_amount = ($cart_item->price + $cart_item->options->product_discount) * ($this->item_discount[$product_id] / 100);

            Cart::instance($this->cart_instance)
                ->update($row_id, [
                    'price' => ($cart_item->price + $cart_item->options->product_discount) - $discount_amount
                ]);

            $this->updateCartOptions($row_id, $product_id, $cart_item, $discount_amount);
        }

        session()->flash('discount_message' . $product_id, 'Discount added to the product!');
    }

    public function calculate($product)
    {
        $price = 0;
        $unit_price = 0;
        $product_tax = 0;
        $sub_total = 0;

        if ($product['product_tax_type'] == 1) {
            $price = $product['product_price'] + ($product['product_price'] * ($product['product_order_tax'] / 100));
            $unit_price = $product['product_price'];
            $product_tax = $product['product_price'] * ($product['product_order_tax'] / 100);
            $sub_total = $product['product_price'] + ($product['product_price'] * ($product['product_order_tax'] / 100));
        } elseif ($product['product_tax_type'] == 2) {
            $price = $product['product_price'];
            $unit_price = $product['product_price'] - ($product['product_price'] * ($product['product_order_tax'] / 100));
            $product_tax = $product['product_price'] * ($product['product_order_tax'] / 100);
            $sub_total = $product['product_price'];
        } else {
            $price = $product['product_price'];
            $unit_price = $product['product_price'];
            $product_tax = 0.00;
            $sub_total = $product['product_price'];
        }

        return ['price' => $price, 'unit_price' => $unit_price, 'product_tax' => $product_tax, 'sub_total' => $sub_total];
    }

    public function updateCartOptions($row_id, $product_id, $cart_item, $discount_amount)
    {
        Cart::instance($this->cart_instance)->update($row_id, ['options' => [
            'sub_total'             => $cart_item->price * $cart_item->qty,
            'code'                  => $cart_item->options->code,
            'stock'                 => $cart_item->options->stock,
            'unit'                 => $cart_item->options->unit,
            'product_tax'           => $cart_item->options->product_tax,
            'unit_price'            => $cart_item->options->unit_price,
            'product_discount'      => $discount_amount,
            'product_discount_type' => $this->discount_type[$product_id],
        ]]);
    }
}
