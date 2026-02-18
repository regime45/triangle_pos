<?php

namespace Modules\Product\Http\Controllers;

use Modules\Product\DataTables\ProductDataTable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    public function index(ProductDataTable $dataTable)
    {
        abort_if(Gate::denies('access_products'), 403);
        return $dataTable->render('product::products.index');
    }

    public function create()
    {
        abort_if(Gate::denies('create_products'), 403);
        $lastCode = Product::orderBy('product_code', 'desc')
            ->value('product_code');

        // If no products yet
        $nextCode = $lastCode
            ? str_pad(((int) $lastCode) + 1, 4, '0', STR_PAD_LEFT)
            : '0001';

        return view('product::products.create', compact('nextCode'));
    }



    // ✅ CSV UPLOAD FORM

    public function printBarcode()
    {
        abort_if(Gate::denies('print_barcodes'), 403);

        return view('product::barcode.index');
    }


    // ✅ CSV UPLOAD HANDLER
    public function uploadCsv(Request $request)
    {
        abort_if(Gate::denies('create_products'), 403);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file')->getRealPath();

        $rowCount = 0;
        $inserted = 0;
        $errors = [];

        if (($handle = fopen($file, 'r')) === false) {
            return back()->withErrors(['csv_file' => 'Failed to open file']);
        }

        $header = fgetcsv($handle, 1000, ',');

        if (count($header) !== 10) {
            fclose($handle);
            return back()->withErrors([
                'csv_file' => 'CSV must have exactly 7 columns'
            ]);
        }

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rowCount++;

                // Skip completely empty rows
                if (count(array_filter($data)) === 0) {
                    continue;
                }

                // Column count check
                if (count($data) !== 10) {
                    $errors[] = "Row {$rowCount}: invalid column count";
                    continue;
                }

                // REQUIRED FIELD
                /*
                $productName = trim($data[0] ?? '');
                if ($productName === '') {
                    $errors[] = "Row {$rowCount}: product_name is empty (skipped)";
                    continue;
                }

                $sku = trim($data[1]) ?: null;

                // ✅ Skip duplicate SKUs
                if ($sku && Product::where('product_sku', $sku)->exists()) {
                    $errors[] = "Row {$rowCount}: SKU '{$sku}' already exists (skipped)";
                    continue;
                }
                    */

                Product::create([
                    'product_name'     => trim($data[0]),
                    'product_sku'      => trim($data[1]),
                    'product_brand'    => trim($data[2]) ?: null,
                    'code'             => trim($data[3]) ?: null,
                    'product_location' => trim($data[4]) ?: null,
                    'product_cost'    => is_numeric($data[5]) ? $data[5] : 0,
                    'product_quantity' => is_numeric($data[6]) ? (int) $data[6] : 0,
                    'product_markup' => is_numeric($data[7]) ? (int) $data[7] : 0,
                    'product_price'    => is_numeric($data[8]) ? $data[8] : 0,
              
                    'product_code' => trim($data[9]) ?: null,
                    'category_id'      => null,
                ]);

                $inserted++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->withErrors(['csv_file' => $e->getMessage()]);
        }

        fclose($handle);

        return back()->with([
            'success' => "Upload completed. Rows: {$rowCount}, Inserted: {$inserted}",
            'csvErrors'  => $errors
        ]);
    }


    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->except('document'));

        if ($request->has('document')) {
            foreach ($request->input('document', []) as $file) {
                $product->addMedia(
                    Storage::path('temp/dropzone/' . $file)
                )->toMediaCollection('images');
            }
        }

        toast('Product Created!', 'success');
        return redirect()->route('products.index');
    }

    public function show(Product $product)
    {
        abort_if(Gate::denies('show_products'), 403);
        return view('product::products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        abort_if(Gate::denies('edit_products'), 403);
        return view('product::products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->except('document'));
        toast('Product Updated!', 'info');
        return redirect()->route('products.index');
    }

    public function destroy(Product $product)
    {
        abort_if(Gate::denies('delete_products'), 403);
        $product->delete();
        toast('Product Deleted!', 'warning');
        return redirect()->route('products.index');
    }
}
