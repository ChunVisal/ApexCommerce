<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Product;
use App\Models\ProductCatalog;
use App\Models\ProductUom;
use App\Models\StockMovement;
use App\Services\Admin\ActivityService;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    use HandlesImageUploads;
    /* =========================================================================
     | 1. CORE PRODUCT CRUD METHODS
     | ========================================================================= */

    public function index(Request $request)
    {
        $categories = Categories::withSum('products as total_stock', 'stock_quantity')->get();

        $products = Product::with('category')
            ->where('has_uom', false)
            ->get();

        if ($request->ajax()) {
            return response()->json($products);
        }

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        try {

            $category = Categories::find($request->category_id);

            if (! $category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                ], 422);
            }

            $code = $this->generateProductCode($request->name);

            do {
                $barcode = str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
            } while (Product::where('barcode', $barcode)->exists());

            $imageUrl = $this->handleProductImage($request);

            $product = Product::create([
                'code' => $code,
                'name' => $request->name,
                'category_id' => $category->id,
                'barcode' => $barcode,
                'selling_price' => $request->selling_price,
                'stock_quantity' => $request->stock_quantity ?? 0,
                'status' => $request->status ?? 'active',
                'cost_price' => $request->cost_price ?? 0,
                'image' => $imageUrl,
                'low_stock_threshold' => $request->low_stock_threshold ?? 5,
            ]);

            // Log initial stock as stock movement
            if ($product->stock_quantity > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $product->stock_quantity,
                    'balance' => $product->stock_quantity,
                    'reason' => 'Initial stock',
                    'notes' => 'Product created with initial stock',
                    'reference' => 'INIT-' . str_pad($product->id, 3, '0', STR_PAD_LEFT) . '-' . now()->format('ymdHi'),
                    'user_id' => Auth::id(),
                ]);
            }

            ActivityService::log('product_created', ' created product: ' . $product->name, 'Products List', 'info');

            return response()->json([
                'success' => true,
                'message' => $product->name . ' created successfully',
                'product' => $product->load('category'),
            ]);
        } catch (\Exception $e) {
            Log::error('Store error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $product = Product::findOrFail($id);

            $imageUrl = $this->handleProductImage($request, $product->image);

            $product->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'selling_price' => $request->selling_price,
                'stock_quantity' => $request->stock_quantity ?? $product->stock_quantity,
                'status' => $request->status ?? $product->status,
                'cost_price' => $request->cost_price ?? $product->cost_price,
                'image' => $imageUrl,
                'low_stock_threshold' => $request->low_stock_threshold ?? $product->low_stock_threshold,
            ]);

            ActivityService::log('product_updated', ' updated product: ' . $product->name, 'Products List', 'info');

            return response()->json([
                'success' => true,
                'message' => $product->name . ' updated successfully',
                'product' => $product->fresh()->load('category'),
            ]);
        } catch (\Exception $e) {
            Log::error('Update error: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        // find product in table if not faild send error message
        $product = Product::findOrFail($id);

        // likes orderItems() is relationship method from models
        if ($product->orderItems()->exists()) {
            return response()->json(['message' => 'Cannot delete: product has orders.'], 422);
        }

        if ($product->cashierStocks()->exists()) {
            return response()->json(['message' => 'Cannot delete: product is allocated to cashiers.'], 422);
        }

        ActivityService::log('product_deleted', ' deleted product: ' . $product->name, 'Products List', 'danger');

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;

        // whereIn find mutiple data Check if ANY of products 1,2,3 have orders 
        if (Product::whereIn('id', $ids)->whereHas('orderItems')->exists()) {
            return response()->json(['message' => 'Some products have orders and cannot be deleted.'], 422);
        }

        if (Product::whereIn('id', $ids)->whereHas('stockMovements', fn($q) => $q->where('reason', '!=', 'Initial stock'))->exists()) {
            return response()->json(['message' => 'Some products have stock history and cannot be deleted.'], 422);
        }

        if (Product::whereIn('id', $ids)->whereHas('cashierStocks')->exists()) {
            return response()->json(['message' => 'Some products are allocated to cashiers.'], 422);
        }

        Product::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Deleted']);
    }

    /* =========================================================================
     | 2. CATEGORY-BASED LOOKUPS
     | ========================================================================= */

    public function byCategory(Request $request)
    {
        // Finds the category matching the given code, but only selects the id column
        $category = Categories::where('code', $request->category_code)->first('id');

        if (! $category) {
            return response()->json(['category_id' => null]);
        }

        // 2. Show extra products that category store in DB not for sell
        $catalogProducts = ProductCatalog::where('category_code', $request->category_code)->get();

        // 3. Show products for that category (real ones already store)
        $dbProducts = Product::where('category_id', $category->id)
            ->select('id', 'name', 'selling_price')
            ->get()->keyBy('name');
        // keyBy() look up a product by name every item searching for a match the name

        $products = $catalogProducts->map(function ($item) use ($dbProducts) {
            // Check if this product in the real database?
            $db = $dbProducts->get($item->name);
            return [
                'id' => $db?->id ?? null,
                'name' => $item->name,
                'selling_price' => $db?->selling_price ?? $item->default_price,
            ];
        })->values();

        return response()->json([
            'category_id' => $category->id,
            'products' => $products,
        ]);
    }

    /* =========================================================================
     | 3. UOM (UNIT OF MEASURE) METHODS
     | ========================================================================= */

    public function indexUoms()
    {
        $categories = Categories::all();
        $products = Product::with('uoms', 'category')->where('has_uom', true)->get();

        $uomCounts = [];
        foreach ($products as $product) {
            foreach ($product->uoms as $uom) {
                $uomCounts[$uom->name] ??= 0;
                $uomCounts[$uom->name]++;
            }
        }

        return view('admin.products-uom.index', compact('products', 'categories', 'uomCounts'));
    }

    public function storeUom(Request $request)
    {
        try {

            $category = Categories::where('code', $request->category_code)->first();

            if (! $category) {
                return response()->json(['error' => 'Category not found'], 422);
            }

            $code = $this->generateProductCode($request->name);

            $imageUrl = $this->handleProductImage($request);

            $product = Product::create([
                'name' => $request->name,
                'category_id' => $category->id,
                'cost_price' => 0,
                'selling_price' => $request->price,
                'stock_quantity' => $request->stock ?? 0,
                'status' => $request->status ?? 'active',
                'has_uom' => true,
                'base_unit_name' => $request->base_unit_name,
                'base_unit_code' => $request->base_unit_code,
                'image' => $imageUrl,
                'code' => $code,
            ]);

            // skip empty/incomplete uoms rows, only bases save real ones.
            if ($request->uoms) {
                $uoms = json_decode($request->uoms, true);
                foreach ($uoms as $uom) {
                    if (! empty($uom['name'])) {
                        $product->uoms()->create($uom);
                    }
                }
            }

            ActivityService::log('uom_product_created', ' created UOM product: ' . $product->name, 'Products UOMs', 'info');

            if ($product->stock_quantity > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'reference' => 'INIT-' . str_pad($product->id, 3, '0', STR_PAD_LEFT) . '-' . now()->format('ymdHi'),
                    'quantity' => $product->stock_quantity,
                    'balance' => $product->stock_quantity,
                    'reason' => 'Initial stock',
                    'notes' => 'Product created with initial stock of ' . $product->stock_quantity . ' ' . ($product->base_unit_code ?: $product->base_unit_name ?: 'unit'),
                    'user_id' => Auth::id(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $product->name . ' created successfully',
                'product' => $product->load(['category', 'uoms']),
            ]);
        } catch (\Exception $e) {
            Log::error('Store UOM error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateUom(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $category = Categories::where('code', $request->category_code)->first();

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 422);
        }

        $imageUrl = $this->handleProductImage($request, $product->image);

        $product->update([
            'category_id' => $category->id,
            'name' => $request->name,
            'selling_price' => $request->price,
            'stock_quantity' => $request->stock ?? $product->stock_quantity,
            'image' => $imageUrl,
            'base_unit_name' => $request->base_unit_name,
            'base_unit_code' => $request->base_unit_code,
            'status' => $request->status,
        ]);

        if ($request->uoms) {
            $product->uoms()->delete();
            $uoms = json_decode($request->uoms, true);
            foreach ($uoms as $uom) {
                $product->uoms()->create($uom);
            }
        }

        ActivityService::log('uom_product_updated', ' updated UOM product: ' . $product->name, 'Products UOMs', 'info');

        return response()->json([
            'success' => true,
            'message' => $product->name . ' updated successfully',
            'product' => $product->fresh()->load(['category', 'uoms']),
        ]);
    }

    public function destroyUom(int $id)
    {
        $product = Product::findOrFail($id);

        if ($product->orderItems()->exists()) {
            return response()->json(['error' => 'Cannot delete: product has existing orders.'], 422);
        }

        if ($product->cashierStocks()->exists()) {
            return response()->json(['error' => 'Cannot delete: product is allocated to cashiers.'], 422);
        }

        $product->uoms()->delete();
        $product->delete();

        ActivityService::log('product_deleted', ' deleted product: ' . $product->name, 'Products UOMs', 'danger');

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }

    /* =========================================================================
     | 4. HELPER METHODS
     | ========================================================================= */

    private function generateProductCode(String $name)
    {
        $prefix = 'PROD-' . strtoupper(substr($name, 0, 3));
        do {
            $code = $prefix . '-' . rand(1000, 9999);
        } while (Product::where('code', $code)->exists());
        return $code;
    }
}
