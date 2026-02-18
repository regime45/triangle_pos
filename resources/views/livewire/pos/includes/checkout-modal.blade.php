<div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cart-check text-primary"></i> Confirm Sale</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('app.pos.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="customer_id" value="{{ $customer_id }}">
                    <input type="hidden" name="tax_percentage" value="{{ $global_tax }}">
                 

                    <input type="hidden" name="discount_type" value="{{ $global_discount_type }}">
<input type="hidden" name="discount_percentage" value="{{ $global_discount }}">

                    <input type="hidden" name="shipping_amount" value="{{ $shipping }}">
                    <input type="hidden" name="display_discount" value="{{ $display_discount }}">
                    <input type="hidden" id="grand_total_raw" value="{{ $grand_total }}">
                    <input type="hidden" id="paid_amount" name="paid_amount" value="{{ $grand_total }}">

                    <div class="row">
                        <div class="col-lg-7">
                            <div class="form-group">
                                <label>Total Amount</label>
                                <input type="hidden" name="total_amount" id="total_amount_hidden" value="{{ $grand_total }}">
                                <!-- Display -->
                                <input type="text" class="form-control" value="{{ $grand_total }}" readonly>

                              
                            </div>

                            <div class="form-group">
                                <label>Received Amount</label>
                                <input type="text" id="paid_amount_display" class="form-control"  value="{{ $grand_total }}" required>
                            </div>

                            <div class="form-group">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Gcash">Gcash</option>
                                    <option value="Credit">Credit</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Note (Optional)</label>
                                <textarea name="note" class="form-control" rows="5"></textarea>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <table class="table table-striped">
                                <tr>
                                    <th>Total Products</th>
                                    <td>{{ Cart::instance($cart_instance ?? 'sale')->count() }}</td>
                                </tr>
                                <tr>
                                    <th>Order Tax ({{ $global_tax }}%)</th>
                                    <td>(+) {{ format_currency(Cart::instance($cart_instance ?? 'sale')->tax()) }}</td>
                                </tr>
                                <tr>
                                    <th>Discount</th>
                                    <td>(-) {{ format_currency($display_discount) }}</td>
                                </tr>
                                <tr>
                                    <th>Shipping</th>
                                    <td>(+) {{ format_currency($shipping) }}</td>
                                </tr>
                                <tr class="text-primary">
                                    <th>Grand Total</th>
                                    <td>{{ format_currency($grand_total) }}</td>
                                </tr>
                                <tr>
                                    <th>Change</th>
                                    <td>
                                        <input type="text" id="change_amount" name="change_amount" class="form-control" value="0.00" readonly>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function formatNumber(value) {
        return Number(value).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function unformat(value) {
        return parseFloat(value.replace(/,/g, '')) || 0;
    }

    function calculateChange() {
        const total = unformat(document.getElementById('grand_total_raw').value);
        const paidInput = document.getElementById('paid_amount_display');
        const paid = unformat(paidInput.value);

        const change = paid > total ? paid - total : 0;

        document.getElementById('change_amount').value = formatNumber(change);
        document.getElementById('paid_amount').value = paid.toFixed(2); // hidden real value
    }

    document.getElementById('paid_amount_display').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9.,]/g, '');
        calculateChange();
    });

    document.getElementById('paid_amount_display').addEventListener('blur', function () {
        const num = unformat(this.value);
        this.value = formatNumber(num);
        calculateChange();
    });

    calculateChange();
</script>
