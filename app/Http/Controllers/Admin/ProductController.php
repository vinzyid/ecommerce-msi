<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const UPLOAD_DIR = 'images/products/uploads';

    public function index(Request $request): View
    {
        $search = $request->validate(['q' => 'nullable|string|max:100'])['q'] ?? null;
        $products = Product::with('category')
            ->when($search, fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated = $this->withUploadedImage($request, $validated);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        Product::query()->create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Produk dibuat.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);
        $validated = $this->withUploadedImage($request, $validated, $product);

        if ($validated['name'] !== $product->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $product);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Produk diperbarui.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'name' => 'required|string|max:120',
            'sku' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product)],
            'description' => 'required|string|max:3000',
            'price' => 'required|numeric|min:0|max:9999999999',
            'compare_at_price' => 'nullable|numeric|min:0|max:9999999999|gte:price',
            'badge' => 'nullable|string|max:30',
            'weight_grams' => 'nullable|integer|min:0|max:1000000',
            'material' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:60',
            'dimensions' => 'nullable|string|max:80',
            'stock' => 'required|integer|min:0|max:1000000',
            'image_url' => ['nullable', 'string', 'max:500', 'regex:/^(https?:\\/\\/|\\/images\\/)[^\\s]+$/'],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ], [
            'compare_at_price.gte' => 'Harga sebelum diskon harus lebih besar atau sama dengan harga jual.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat JPG, PNG, atau WEBP.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);
    }

    /**
     * Simpan foto yang diunggah ke folder publik dan hapus foto unggahan lama.
     */
    private function withUploadedImage(Request $request, array $validated, ?Product $product = null): array
    {
        unset($validated['image']);

        if (! $request->hasFile('image')) {
            return $validated;
        }

        $file = $request->file('image');
        $name = $file->hashName();
        $file->move(public_path(self::UPLOAD_DIR), $name);
        $validated['image_url'] = '/'.self::UPLOAD_DIR.'/'.$name;

        if (str_starts_with((string) $product?->image_url, '/'.self::UPLOAD_DIR.'/')) {
            File::delete(public_path(ltrim($product->image_url, '/')));
        }

        return $validated;
    }

    private function uniqueSlug(string $name, ?Product $ignore = null): string
    {
        $base = Str::slug($name) ?: 'produk';
        $slug = $base;
        $number = 2;

        while (Product::query()->where('slug', $slug)->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))->exists()) {
            $slug = $base.'-'.$number++;
        }

        return $slug;
    }
}
