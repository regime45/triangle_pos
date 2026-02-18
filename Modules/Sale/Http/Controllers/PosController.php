<?php

namespace Modules\Sale\Http\Controllers;

use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\People\Entities\Customer;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleDetails;
use Modules\Sale\Entities\SalePayment;
use Modules\Sale\Http\Requests\StorePosSaleRequest;

class PosController extends Controller
{

    public function index()
    {
        Cart::instance('sale')->destroy();

        $customers = Customer::all();
        $product_categories = Category::all();

        return view('sale::pos.index', compact('product_categories', 'customers'));
    }


    public function store(StorePosSaleRequest $request)
    {
        $saleId = DB::transaction(function () use ($request) {

            // --- AMOUNTS ---
            $totalAmount = $request->total_amount;
            $paidAmount  = $request->paid_amount;
            $dueAmount   = $totalAmount - $paidAmount;

            // --- PAYMENT STATUS ---
            if ($dueAmount == $totalAmount) {
                $payment_status = 'Unpaid';
            } elseif ($dueAmount > 0) {
                $payment_status = 'Partial';
            } else {
                $payment_status = 'Paid';
            }

            // --- DISCOUNT LOGIC ---
            $discountPercentage = 0;
            $discountAmount     = 0;

            if ($request->discount_type === 'percentage') {
                $discountPercentage = $request->discount_percentage;
                $discountAmount = ($totalAmount * ($discountPercentage / 100)) * 100;
            }

            if ($request->discount_type === 'fixed') {
                $discountAmount = $request->discount_percentage * 100;
            }

            // --- CREATE SALE ---
            $sale = Sale::create([
                'date' => now()->format('Y-m-d'),
                'reference' => 'PSL',

                'customer_id'   => $request->customer_id,
                'customer_name' => Customer::findOrFail($request->customer_id)->customer_name,

                'tax_percentage'      => $request->tax_percentage,
                'discount_percentage' => $discountPercentage,
                'discount_amount'     => $discountAmount,
                'change_amount'       => $request->change_amount * 100,

                'shipping_amount' => $request->shipping_amount * 100,
                'paid_amount'     => $paidAmount * 100,
                'total_amount'    => $totalAmount * 100,
                'due_amount'      => $dueAmount * 100,

                'status'         => 'Completed',
                'payment_status' => $payment_status,
                'payment_method' => $request->payment_method,
                'note'           => $request->note,

                'tax_amount' => Cart::instance('sale')->tax() * 100,
            ]);

            // --- SALE DETAILS ---
            foreach (Cart::instance('sale')->content() as $cart_item) {

                SaleDetails::create([
                    'sale_id' => $sale->id,
                    'product_id' => $cart_item->id,
                    'product_name' => $cart_item->name,
                    'product_code' => $cart_item->options->code,
                    'quantity' => $cart_item->qty,
                    'price' => $cart_item->price * 100,
                    'unit_price' => $cart_item->options->unit_price * 100,
                    'sub_total' => $cart_item->options->sub_total * 100,
                    'product_discount_amount' => $cart_item->options->product_discount * 100,
                    'product_discount_type' => $cart_item->options->product_discount_type,
                    'product_tax_amount' => $cart_item->options->product_tax * 100,
                ]);

                Product::where('id', $cart_item->id)
                    ->decrement('product_quantity', $cart_item->qty);
            }

            Cart::instance('sale')->destroy();

            // --- PAYMENT ---
            if ($sale->paid_amount > 0) {
                SalePayment::create([
                    'date' => now()->format('Y-m-d'),
                    'reference' => 'INV/' . $sale->reference,
                    'amount' => $sale->paid_amount,
                    'sale_id' => $sale->id,
                    'payment_method' => $request->payment_method
                ]);
            }

            return $sale->id; // 🔥 IMPORTANT
        });

        toast('POS Sale Created!', 'success');


        // Flash sale ID to session to trigger JS popup
        //session()->flash('print_sale_id', $saleId);

        // Redirect back to POS page
        return redirect()->route('app.pos.index');
    }
}
