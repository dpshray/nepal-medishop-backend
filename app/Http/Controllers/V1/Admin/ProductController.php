<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Models\Variant;
use App\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ProductController extends Controller
{
    //
    use ResponseTrait;
    function add_product(ProductRequest $request)
    {

        $slug = Str::slug($request->name) . '-' . strtolower(Str::random(10));
        DB::beginTransaction(); // Wrap in transaction for atomicity

        try {
            // Create Product
            $product = Product::create([
                'name' => $request->name,
                'slug' => $slug,
                'model_no' => $request->model_no,
                'description' => $request->description,
                'price' => $request->price,
                'discount_price' => $request->discount_price ?? null,
                'pattern'=>$request->pattern,
                'fabric'=>$request->fabric,
                'material'=>$request->material,

            ]);

            if (!$product) {
                return $this->apiError("Failed to add product");
            }

            // Attach categories
            $product->categories()->attach($request->categories);
            // Attach product images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $product->addMedia($image)->toMediaCollection(Product::MEDIA_NAME);
                }
            }

            // Create variants and attach images
            foreach ($request->variant as $index => $var) {
                $variant = Variant::create([
                    'product_id' => $product->id,
                    'size' => $var['size'],
                    'color' => $var['color'],
                    'price' => $var['price'],
                    'discount_price' => $var['discount_price'] ?? null,
                    'stock' => $var['stock'],
                ]);

                if (!$variant) {
                    DB::rollBack();
                    return $this->apiError("Failed to add variant");
                }

                if ($request->hasFile("variant.$index.images")) {
                    foreach ($request->file("variant.$index.images") as $image) {
                        $variant->addMedia($image)->toMediaCollection(Variant::MEDIA_NAME);
                    }
                }
            }

            DB::commit();
            return $this->apiSuccess("Product added successfully", $product);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiError("Something went wrong: " . $e->getMessage());
        }
    }

    function update_product(UpdateProductRequest $request, Product $product)
    {
        DB::beginTransaction();

        try {
            //Update product fields
            $product->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name) . '-' . strtolower(Str::random(10)),
                'description' => $request->description,
                'price' => $request->price,
                'discount_price' => $request->discount_price ?? null,
                'pattern'=>$request->pattern,
                'fabric'=>$request->fabric,
                'material'=>$request->material,
            ]);

            //Update product images
            if ($request->hasFile('images')) {
                //clear old images first
                $product->clearMediaCollection(Product::MEDIA_NAME);

                foreach ($request->file('images') as $image) {
                    $product->addMedia($image)->toMediaCollection(Product::MEDIA_NAME);
                }
            }

            //Sync categories
            $product->categories()->sync($request->categories);

            //Handle variants
            $existingVariantIds = $product->variants()->pluck('id')->toArray();
            $incomingVariantIds = [];

            foreach ($request->variant as $variantData) {
                if (isset($variantData['id'])) {
                    // Update existing variant
                    $variant = Variant::find($variantData['id']);
                    if ($variant && $variant->product_id === $product->id) {
                        $variant->update([
                            'size' => $variantData['size'],
                            'color' => $variantData['color'],
                            'price' => $variantData['price'],
                            'discount_price' => $variantData['discount_price'] ?? null,
                            'stock' => $variantData['stock'],
                        ]);
                        if (!empty($variantData['images'])) {
                            $variant->clearMediaCollection(Variant::MEDIA_NAME);
                            foreach ($variantData['images'] as $image) {
                                $variant->addMedia($image)->toMediaCollection(Variant::MEDIA_NAME);
                            }
                        }

                        $incomingVariantIds[] = $variant->id;
                    }
                } else {
                    // Create new variant
                    $newVariant = Variant::create([
                        'product_id' => $product->id,
                        'size' => $variantData['size'],
                        'color' => $variantData['color'],
                        'price' => $variantData['price'],
                        'discount_price' => $variantData['discount_price'] ?? null,
                        'stock' => $variantData['stock'],
                    ]);

                    if (isset($variantData['images'])) {
                        foreach ($variantData['images'] as $image) {
                            $newVariant->addMedia($image)->toMediaCollection(Variant::MEDIA_NAME);
                        }
                    }

                    $incomingVariantIds[] = $newVariant->id;
                }
            }

            //Delete removed variants
            $variantsToDelete = array_diff($existingVariantIds, $incomingVariantIds);
            Variant::destroy($variantsToDelete);

            DB::commit();
            return $this->apiSuccess("Product updated successfully", $product->load('variants', 'categories'));
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiError("Something went wrong: " . $e->getMessage());
        }
    }

    public function delete_product(Product $product)
    {
        DB::beginTransaction();

        try {
            foreach ($product->variants as $variant) {
                $variant->clearMediaCollection(Variant::MEDIA_NAME);
                $variant->delete();
            }
            $product->clearMediaCollection(Product::MEDIA_NAME);

            $product->categories()->detach();

            $product->delete();

            DB::commit();

            return $this->apiSuccess("Product deleted successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiError("Failed to delete product: " . $e->getMessage());
        }
    }
}
