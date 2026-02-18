@extends('layouts.app')

@section('title', 'Edit Product')

@section('breadcrumb')
<ol class="breadcrumb border-0 m-0">
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')
<div class="container-fluid mb-4">
    <form id="product-form" action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('patch')
        <div class="row">

            {{-- Alerts & Submit --}}
            <div class="col-lg-12 mb-3">
                @include('utils.alerts')
                <button class="btn btn-primary">Update Product <i class="bi bi-check"></i></button>
            </div>

            {{-- Product Details Card --}}
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        {{-- Name & Code --}}
                        <div class="form-row mb-4">
                            <div class="col-md-4">
                                <label>Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="product_name" required value="{{ $product->product_name }}">
                            </div>
                            <div class="col-md-4">
                                <label>Barcode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="product_code" readonly value="{{ $product->product_code }}">
                            </div>

                            <div class="col-md-4">
                                <label>Product Code<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="code" required value="{{ $product->code }}">
                            </div>
                        </div>

                        {{-- Category & Barcode --}}
                        <div class="form-row mb-3">
                            <div class="col-md-6">
                                <label>Category <span class="text-danger">*</span></label>
                                <select class="form-control" name="category_id" required>
                                    @foreach(\Modules\Product\Entities\Category::all() as $category)
                                        <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Barcode Symbology <span class="text-danger">*</span></label>
                                <select class="form-control" name="product_barcode_symbology" required>
                                    @php $sym = $product->product_barcode_symbology; @endphp
                                    <option value="C128" {{ $sym=='C128'?'selected':'' }}>Code 128</option>
                                    <option value="C39" {{ $sym=='C39'?'selected':'' }}>Code 39</option>
                                    <option value="UPCA" {{ $sym=='UPCA'?'selected':'' }}>UPC-A</option>
                                    <option value="UPCE" {{ $sym=='UPCE'?'selected':'' }}>UPC-E</option>
                                    <option value="EAN13" {{ $sym=='EAN13'?'selected':'' }}>EAN-13</option>
                                    <option value="EAN8" {{ $sym=='EAN8'?'selected':'' }}>EAN-8</option>
                                </select>
                            </div>
                        </div>

                        {{-- Cost, Markup & Price --}}
                        <div class="form-row mb-3">
                            <div class="col-md-4">
                                <label>Cost <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="product_cost" name="product_cost" required value="{{ $product->product_cost }}">
                            </div>
                            <div class="col-md-4">
                                <label>Markup (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="product_markup" name="product_markup" required value="{{ $product->product_markup }}">
                            </div>
                            <div class="col-md-4">
                                <label>Price <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="product_price" name="product_price" required value="{{ $product->product_price }}">
                            </div>
                        </div>

                        {{-- Quantity & Stock Alert --}}
                        <div class="form-row mb-3">
                            <div class="col-md-6">
                                <label>Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="product_quantity" min="1" required value="{{ $product->product_quantity }}">
                            </div>
                            <div class="col-md-6">
                                <label>Alert Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="product_stock_alert" min="0" required value="{{ $product->product_stock_alert }}">
                            </div>
                        </div>

                        {{-- SKU, Year, Model, Brand, Location --}}
                        <div class="form-row mb-3">
                            <div class="col-md-2">
                                <label>Part Number</label>
                                <input type="text" class="form-control" name="product_sku" value="{{ $product->product_sku }}">
                            </div>
                            <div class="col-md-2">
                                <label>Year</label>
                                <input type="text" class="form-control" name="product_year" value="{{ $product->product_year }}">
                            </div>
                            <div class="col-md-2">
                                <label>Model</label>
                                <input type="text" class="form-control" name="product_model" value="{{ $product->product_model }}">
                            </div>
                            <div class="col-md-2">
                                <label>Brand</label>
                                <input type="text" class="form-control" name="product_brand" value="{{ $product->product_brand }}">
                            </div>
                            <div class="col-md-4">
                                <label>Location/Yard</label>
                                <input type="text" class="form-control" name="product_location" value="{{ $product->product_location }}">
                            </div>
                        </div>

                        {{-- Tax & Unit --}}
                        <div class="form-row mb-3">
                            <div class="col-md-4">
                                <label>Tax (%)</label>
                                <input type="number" class="form-control" name="product_order_tax" min="0" max="100" value="{{ $product->product_order_tax }}">
                            </div>
                            <div class="col-md-4">
                                <label>Tax Type</label>
                                <select class="form-control" name="product_tax_type">
                                    <option value="" {{ $product->product_tax_type==null?'selected':'' }}>None</option>
                                    <option value="1" {{ $product->product_tax_type==1?'selected':'' }}>Exclusive</option>
                                    <option value="2" {{ $product->product_tax_type==2?'selected':'' }}>Inclusive</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Unit <span class="text-danger">*</span></label>
                                <select class="form-control" name="product_unit" required>
                                    <option value="" selected>Select Unit</option>
                                    @foreach(\Modules\Setting\Entities\Unit::all() as $unit)
                                        <option value="{{ $unit->short_name }}" {{ $product->product_unit==$unit->short_name?'selected':'' }}>
                                            {{ $unit->name }} | {{ $unit->short_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="product_note" class="form-control" rows="4">{{ $product->product_note }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Product Images --}}
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <label>Product Images</label>
                        <div class="dropzone d-flex align-items-center justify-content-center" id="document-dropzone">
                            <div class="dz-message"><i class="bi bi-cloud-arrow-up"></i></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@section('third_party_scripts')
<script src="{{ asset('js/dropzone.js') }}"></script>
<script src="{{ asset('js/jquery-mask-money.js') }}"></script>
@endsection

@push('page_scripts')
<script>
$(document).ready(function(){

    // MaskMoney
    $('#product_cost, #product_price').maskMoney({
        prefix:'{{ settings()->currency->symbol }}',
        thousands:'{{ settings()->currency->thousand_separator }}',
        decimal:'{{ settings()->currency->decimal_separator }}',
        allowZero:true,
        precision:2
    }).maskMoney('mask');

    // Calculate price based on cost + markup %
    const costInput = $('#product_cost');
    const markupInput = $('#product_markup');
    const priceInput = $('#product_price');

    function calculatePrice(){
        const cost = parseFloat(costInput.maskMoney('unmasked')[0] || 0);
        const markup = parseFloat(markupInput.val() || 0);
        const price = cost + (cost * markup / 100);
        priceInput.maskMoney('mask', price);
    }

    costInput.on('input', calculatePrice);
    markupInput.on('input', calculatePrice);

    // Unmask before submit
    $('#product-form').submit(function(){
        costInput.val(costInput.maskMoney('unmasked')[0].toFixed(2));
        priceInput.val(priceInput.maskMoney('unmasked')[0].toFixed(2));
    });

    // Initialize Dropzone
    var uploadedDocumentMap = {};
    Dropzone.options.documentDropzone = {
        url: '{{ route('dropzone.upload') }}',
        maxFilesize: 1,
        acceptedFiles: '.jpg,.jpeg,.png',
        maxFiles: 3,
        addRemoveLinks: true,
        dictRemoveFile: "<i class='bi bi-x-circle text-danger'></i> remove",
        headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"},
        success: function(file, response){
            $('form').append('<input type="hidden" name="document[]" value="'+response.name+'">');
            uploadedDocumentMap[file.name] = response.name;
        },
        removedfile: function(file){
            file.previewElement.remove();
            var name = uploadedDocumentMap[file.name];
            $('form').find('input[name="document[]"][value="'+name+'"]').remove();
        },
        init: function(){
            @if($product->getMedia('images')->count())
                var files = {!! json_encode($product->getMedia('images')) !!};
                for(var i in files){
                    var file = files[i];
                    this.options.addedfile.call(this, file);
                    this.options.thumbnail.call(this, file, file.original_url);
                    file.previewElement.classList.add('dz-complete');
                    $('form').append('<input type="hidden" name="document[]" value="'+file.file_name+'">');
                }
            @endif
        }
    };
});
</script>
@endpush
