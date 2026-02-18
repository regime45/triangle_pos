<div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div>
                @if (session()->has('message'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <div class="alert-body">
                        <span>{{ session('message') }}</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                </div>
                @endif

                <div class="form-group">
                    <label for="customer_id">Customer <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i>
                            </a>
                        </div>
                        <select wire:model.live="customer_id" id="customer_id" class="form-control">
                            <option value="" selected>Select Customer</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr class="text-center">
                                <th class="align-middle">Product</th>
                                <th class="align-middle">Price</th>
                                <th class="align-middle">Quantity</th>
                                <th class="align-middle">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($cart_items->isNotEmpty())
                            @foreach($cart_items as $cart_item)
                            <tr>
                                <td class="align-middle">
                                    {{ $cart_item->name }} <br>
                                    <span class="badge badge-success">
                                        {{ $cart_item->options->code }}
                                    </span>
                                    @include('livewire.includes.product-cart-modal')
                                </td>

                                <td class="align-middle">
                                    {{ format_currency($cart_item->price) }}
                                </td>

                                <td class="align-middle">
                                    @include('livewire.includes.product-cart-quantity')
                                </td>

                                <td class="align-middle text-center">
                                    <a href="#" wire:click.prevent="removeItem('{{ $cart_item->rowId }}')">
                                        <i class="bi bi-x-circle font-2xl text-danger"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="8" class="text-center">
                                    <span class="text-danger">
                                        Please search & select products!
                                    </span>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">


                        <table class="table table-striped">
                            <tr>
                                <th>Order Tax ({{ $global_tax }}%)</th>
                                <td>(+) {{ format_currency(Cart::instance($cart_instance)->tax()) }}</td>
                            </tr>
                            <tr>
                                <th>
                                    Discount
                                    @if($global_discount_type === 'percentage')
                                    ({{ $global_discount }}%)
                                    @else
                                    (₱{{ number_format($display_discount, 2) }})
                                    @endif
                                </th>
                                <td>(-) {{ format_currency($display_discount) }}</td>
                            </tr>
                            <tr>
                                <th>Shipping</th>
                                <td>(+) {{ format_currency($shipping) }}</td>
                            </tr>

                          <tr>
                                <th>  Charges
                                    @if($global_charges_type === 'percentage')
                                    ({{ $global_charges }}%)
                                    @else
                                    (₱{{ number_format($display_charges, 2) }})
                                    @endif</th>
                                <td>(+) {{ format_currency($global_charges) }}</td>
                            </tr>
                            <tr class="text-primary">
                                <th>Grand Total</th>
                                <th>(=) {{ format_currency($grand_total) }}</th>
                            </tr>
                        </table>





                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="tax_percentage">Order Tax (%)</label>
                        <input wire:model.blur="global_tax" type="number" class="form-control" min="0" max="100" value="{{ $global_tax }}" required>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">

                        <div class="form-group">
                            <label>Discount Type</label>
                            <select wire:model.live="global_discount_type" class="form-control">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>


                        <input
                            wire:model.live="global_discount"

                            type="number"
                            class="form-control"
                            min="0"
                            @if($this->global_discount_type === 'percentage')

                        @endif
                        step="0.01"
                        required
                        >

                    </div>

                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="shipping_amount">Shipping</label>
                        <input wire:model.blur="shipping" type="number" class="form-control" min="0" value="0" required step="0.01">
                    </div>
                </div>


                 <div class="col-lg-3">
                      <div class="form-group">

                        <div class="form-group">
                            <label>Charges Type</label>
                            <select wire:model.live="global_charges_type" class="form-control">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>


                        <input
                            wire:model.live="global_charges"

                            type="number"
                            class="form-control"
                            min="0"
                            @if($this->global_charges_type === 'percentage')

                        @endif
                        step="0.01"
                        required
                        >

                    </div>
                </div>


            </div>

            <div class="form-group d-flex justify-content-center flex-wrap mb-0">
                <button wire:click="resetCart" type="button" class="btn btn-pill btn-danger mr-3"><i class="bi bi-x"></i> Reset</button>
                <button wire:loading.attr="disabled" wire:click="proceed" type="button" class="btn btn-pill btn-primary" {{  $total_amount == 0 ? 'disabled' : '' }}><i class="bi bi-check"></i> Proceed</button>
            </div>
        </div>
    </div>

    {{--Checkout Modal--}}
    @include('livewire.pos.includes.checkout-modal')

</div>








@if(session()->has('print_sale_id'))
@php $saleId = session('print_sale_id'); @endphp
<script>
    /*
document.addEventListener('DOMContentLoaded', () => {
    const saleId = @json($saleId);
    if (!saleId) return;

    // Create a hidden iframe to hold the receipt HTML
    const iframe = document.createElement('iframe');
    iframe.style.position = 'absolute';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    iframe.style.visibility = 'hidden';
    iframe.src = `/sales/pos/print-html/${saleId}`;
    document.body.appendChild(iframe);

    // Wait until iframe content loads, then print
    iframe.onload = function() {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch(e) {
            console.warn("Auto-print blocked. User interaction may be needed.");
        }

        // Remove iframe after printing
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 1000);
    };
});
*/
</script>
@php session()->forget('print_sale_id'); @endphp
@endif
