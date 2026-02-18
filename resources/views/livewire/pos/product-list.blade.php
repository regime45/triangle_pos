<div> <!-- Root wrapper for Livewire -->
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body p-2">
            <livewire:pos.filter :categories="$categories"/>

            <div class="row position-relative">
                <!-- Loading Overlay -->
                <div wire:loading.flex 
                     class="col-12 position-absolute justify-content-center align-items-center" 
                     style="top:0;right:0;left:0;bottom:0;background-color: rgba(255,255,255,0.5);z-index: 99;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>

                <!-- Products -->
                @forelse($products as $product)
                    <div wire:click.prevent="selectProduct({{ $product }})" 
                         class="col-6 col-md-4 col-lg-3 col-xl-2 mb-2" 
                         style="cursor: pointer;">
                        <div class="card border-0 shadow-sm h-100 small-card">
                            <div class="position-relative">
                                <img src="{{ $product->getFirstMediaUrl('images') }}" 
                                     class="card-img-top" 
                                     style="height: 120px; object-fit: cover;" 
                                     alt="Product Image">
                                <div class="badge badge-info position-absolute" 
                                     style="left:5px; top:5px; font-size: 10px;">
                                    Stock: {{ $product->product_quantity }}
                                </div>
                            </div>
                            <div class="card-body p-1 text-center">
                                <h6 class="card-title mb-1" style="font-size: 12px;">
                                    {{ $product->product_name }}
                                </h6>
                                <span class="badge badge-success" style="font-size: 10px;">
                                    {{ $product->product_code }}
                                </span>
                                <p class="card-text font-weight-bold mb-0" style="font-size: 12px;">
                                    {{ format_currency($product->product_price) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning mb-0 text-center">
                            Products Not Found...
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div @class(['mt-2' => $products->hasPages()])>
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
