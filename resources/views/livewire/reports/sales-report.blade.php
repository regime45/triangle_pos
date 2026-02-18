<div>
    {{-- FILTER --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form wire:submit.prevent="generateReport">
                        <div class="form-row">
                            <div class="col-lg-4">
                                <label>Start Date *</label>
                                <input type="date" wire:model="start_date" class="form-control">
                                @error('start_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-lg-4">
                                <label>End Date *</label>
                                <input type="date" wire:model="end_date" class="form-control">
                                @error('end_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-lg-4">
                                <label>Customer</label>
                                <select wire:model="customer_id" class="form-control">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->customer_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row mt-3">
                            <div class="col-lg-6">
                                <label>Status</label>
                                <select wire:model="sale_status" class="form-control">
                                    <option value="">All</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Shipped">Shipped</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>

                            <div class="col-lg-6">
                                <label>Payment Status</label>
                                <select wire:model="payment_status" class="form-control">
                                    <option value="">All</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Partial">Partial</option>
                                    <option value="Unpaid">Unpaid</option>
                                </select>
                            </div>
                        </div>

                        <button class="btn btn-primary mt-3">
                            Filter Report
                        </button>

                        <button type="button"
                            wire:click="generatePdf"
                            class="btn btn-danger mt-3">
                            Generate Report PDF
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body position-relative">

                    <div wire:loading.flex class="position-absolute w-100 h-100 justify-content-center align-items-center"
                        style="background:rgba(255,255,255,.6);z-index:99">
                        <div class="spinner-border text-primary"></div>
                    </div>

                    <table class="table table-bordered table-striped text-center">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Customer</th>
                                <th>Item Name| Part Number</th> {{-- ✅ NEW --}}
                                <th>Status</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($sales as $sale)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</td>
                                <td>{{ $sale->reference }}</td>
                                <td>{{ $sale->customer_name }}</td>

                                {{-- PRODUCTS LIST --}}
                                {{-- PRODUCTS LIST --}}
                                <td class="text-left">
                                    @foreach ($sale->saleDetails as $detail)
                                    <div>
                                        {{ $detail->product->product_name ?? 'N/A' }}
                                        - {{ $detail->product->product_sku ?? '' }}
                                        <small class="text-muted">
                                            (Qty: {{ $detail->quantity }})
                                        </small>
                                    </div>
                                    @endforeach
                                </td>


                                <td>
                                    <span class="badge badge-{{ $sale->status == 'Completed' ? 'success' : ($sale->status == 'Shipped' ? 'primary' : 'info') }}">
                                        {{ $sale->status }}
                                    </span>
                                </td>

                                <td>{{ format_currency($sale->total_amount) }}</td>
                                <td>{{ format_currency($sale->paid_amount) }}</td>
                                <td>{{ format_currency($sale->due_amount) }}</td>

                                <td>
                                    <span class="badge badge-{{ $sale->payment_status == 'Paid' ? 'success' : ($sale->payment_status == 'Partial' ? 'warning' : 'danger') }}">
                                        {{ $sale->payment_status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-danger">
                                    No Sales Data Available
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div>
</div>