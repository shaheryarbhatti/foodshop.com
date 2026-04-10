<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Allergy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'products.manage', ['products.view', 'products.add', 'products.edit', 'products.delete', 'products.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = Product::with(['category', 'allergies'])->select('products.*');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('bulk_select', fn ($product) => '<input type="checkbox" class="form-check-input row-checkbox" value="' . $product->id . '">')
                ->addColumn('image_preview', function ($product) {
                    if ($product->image && file_exists(public_path($product->image))) {
                        return '<img src="' . asset('public/' . $product->image) . '" alt="' . e($product->title) . '" style="width:52px;height:52px;object-fit:cover;border-radius:10px;border:1px solid #eee;">';
                    }

                    return '<span class="badge bg-light text-dark">' . __('no_image') . '</span>';
                })
                ->addColumn('category_name', fn ($product) => e(optional($product->category)->title ?: '-'))
                ->addColumn('price_label', fn ($product) => number_format((float) $product->base_price, 2))
                ->addColumn('allergies_list', function ($product) {
                    if ($product->allergies->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }
                    return $product->allergies->map(fn($a) => '<span class="badge bg-info text-white">' . e($a->code) . '</span>')->join(' ');
                })
                ->addColumn('status_badge', function ($product) {
                    return $product->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('action', function ($product) use ($user) {
                    $canEdit = $this->userCanAny($user, ['products.edit']);
                    $canDelete = $this->userCanAny($user, ['products.delete']);

                    $edit = $canEdit ? '<a href="' . route('products.edit', $product->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>' : '';
                    $delete = $canDelete ? '<form action="' . route('products.destroy', $product->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>' : '';

                    return $edit || $delete ? '<div class="action d-flex gap-2">' . $edit . $delete . '</div>' : '<span class="text-muted">-</span>';
                })
                ->rawColumns(['bulk_select', 'image_preview', 'allergies_list', 'status_badge', 'action'])
                ->make(true);
        }

        return view('products.manage');
    }

    public function create()
    {
        $this->authorizeAction('products.add');

        return view('products.add', [
            'categories' => Category::where('status', true)->orderBy('title')->get(),
            'allergies' => Allergy::where('status', true)->orderBy('title')->get(),
            'nextSerialNumber' => ((int) Product::max('serial_number')) + 1,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('products.add');

        $validated = $this->validateProduct($request);

        if ($request->hasFile('image')) {
            $directory = public_path('uploads/products');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $file = $request->file('image');
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move($directory, $fileName);
            $validated['image'] = 'uploads/products/' . $fileName;
        }

        $product = Product::create($validated);

        if ($request->has('allergy_ids')) {
            $product->allergies()->sync($request->allergy_ids);
        }

        return redirect()->route('products.manage')->with('success', __('product_created_successfully'));
    }

    public function edit(Product $product)
    {
        $this->authorizeAction('products.edit');

        return view('products.edit', [
            'product' => $product,
            'categories' => Category::where('status', true)->orderBy('title')->get(),
            'allergies' => Allergy::where('status', true)->orderBy('title')->get(),
            'nextSerialNumber' => ((int) Product::max('serial_number')) + 1,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeAction('products.edit');

        $validated = $this->validateProduct($request, $product);

        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }

            $directory = public_path('uploads/products');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $file = $request->file('image');
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move($directory, $fileName);
            $validated['image'] = 'uploads/products/' . $fileName;
        }

        $product->update($validated);

        if ($request->has('allergy_ids')) {
            $product->allergies()->sync($request->allergy_ids);
        } else {
            $product->allergies()->detach();
        }

        return redirect()->route('products.manage')->with('success', __('product_updated_successfully'));
    }

    public function destroy(Product $product)
    {
        $this->authorizeAction('products.delete');

        if ($product->image && file_exists(public_path($product->image))) {
            @unlink(public_path($product->image));
        }

        $product->addons()->detach();
        $product->delete();

        return redirect()->route('products.manage')->with('success', __('product_deleted_successfully'));
    }

    public function destroyAll()
    {
        $this->authorizeAction('products.delete');

        $productIds = request()->input('selected_ids', []);
        if (! is_array($productIds) || empty($productIds)) {
            return redirect()->route('products.manage')->with('error', __('select_records_to_delete'));
        }

        Product::query()->with('addons')->whereIn('id', $productIds)->get()->each(function ($product) {
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }

            $product->addons()->detach();
            $product->delete();
        });

        return redirect()->route('products.manage')->with('success', __('all_products_deleted_successfully'));
    }

    public function exportExcel(): StreamedResponse
    {
        $this->authorizeAction('products.manage');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');

        $headers = ['Product ID', 'Product Title', 'Price', 'Description'];
        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        $products = Product::query()
            ->orderBy('id')
            ->get(['id', 'title', 'base_price', 'description']);

        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue("A{$row}", $product->id);
            $sheet->setCellValue("B{$row}", $product->title);
            $sheet->setCellValue("C{$row}", (float) $product->base_price);
            $sheet->setCellValue("D{$row}", (string) ($product->description ?? ''));
            $row++;
        }

        foreach (range(1, count($headers)) as $columnIndex) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setAutoSize(true);
        }

        $fileName = 'products-bulk-update-' . now()->format('Y-m-d-His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorizeAction('products.edit');

        $validated = $request->validate([
            'products_excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $spreadsheet = IOFactory::load($validated['products_excel_file']->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        if (count($rows) <= 1) {
            return redirect()->route('products.manage')->with('error', __('products_excel_empty_file'));
        }

        $updatedCount = 0;

        DB::transaction(function () use ($rows, &$updatedCount) {
            foreach (array_slice($rows, 1) as $row) {
                $productId = isset($row['A']) ? (int) trim((string) $row['A']) : 0;
                if ($productId <= 0) {
                    continue;
                }

                $product = Product::find($productId);
                if (! $product) {
                    continue;
                }

                $title = trim((string) ($row['B'] ?? ''));
                $priceCell = $row['C'] ?? null;
                $description = trim((string) ($row['D'] ?? ''));

                if ($title === '') {
                    continue;
                }

                if ($priceCell === null || $priceCell === '') {
                    continue;
                }

                $price = is_numeric($priceCell) ? (float) $priceCell : (float) preg_replace('/[^0-9.\-]/', '', (string) $priceCell);
                if ($price < 0) {
                    $price = 0;
                }

                $updateData = [
                    'title' => $title,
                    'base_price' => $price,
                    'description' => $description,
                ];

                if ($product->title !== $title) {
                    $updateData['permalink'] = $this->generateUniquePermalink($title, $product->id);
                }

                $product->update($updateData);
                $updatedCount++;
            }
        });

        return redirect()->route('products.manage')->with(
            'success',
            trans_choice('products_excel_import_success', $updatedCount, ['count' => $updatedCount])
        );
    }

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, [$permission])) {
            abort(403);
        }
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $productId = $product?->id;

        return $request->validate([
            'serial_number' => 'required|integer|min:1|unique:products,serial_number,' . $productId,
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255|unique:products,title,' . $productId,
            'permalink' => 'nullable|string|max:255|alpha_dash|unique:products,permalink,' . $productId,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);
    }

    private function generateUniquePermalink(string $title, ?int $ignoreProductId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'product';
        $permalink = $base;
        $counter = 2;

        while (
            Product::query()
                ->when($ignoreProductId, fn ($query) => $query->where('id', '!=', $ignoreProductId))
                ->where('permalink', $permalink)
                ->exists()
        ) {
            $permalink = $base . '-' . $counter;
            $counter++;
        }

        return $permalink;
    }
}
