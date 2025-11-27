<?php

namespace App\Http\Controllers\Api\V1\BulkUpload;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BulkUploadMainController extends Controller
{
    use ResponseTrait;

    private function getExcelRowInArray(UploadedFile $file, bool $in_chunk = true) {
        $spreadsheet = IOFactory::load($file->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();
        array_shift($rows);
        return $in_chunk ? array_chunk($rows, 100) : $rows;
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/bulk-upload/product",
     *     summary="Store a product tag",
     *     description="Store a product tag.",
     *     operationId="BulkProductUpload",
     *     tags={"BulkUpload"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Upload file",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="file file to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag bulk upload response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="'Tag upload completed.'"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function productBulkUpload(Request $request) {

        $data = $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv'
        ]);
        $chunked_data = $this->getExcelRowInArray($data['file']);
        $total_bulk_upload_errors_count = 0;
        try {
            // Log::info("variations", $rows);
            DB::transaction(function () use ($chunked_data) {

                foreach ($chunked_data as $chunk) {
                    foreach ($chunk as $row) {
                        $productData = [
                            'status' => true,
                            'product_id' => $row[0],
                            'is_featured' => filter_var($row[1], FILTER_VALIDATE_BOOLEAN),
                            'brand_id' => $row[2],
                            'generic_product_name_id' => $row[3],
                            'name' => $row[4],
                            'description' => $row[5],
                            'discount_percent' => is_numeric($row[6]) ? (float)$row[6] : null,
                            'prescription_required' => filter_var($row[7], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                            'added_by' => Auth::id()
                        ];

                        $product = Product::create($productData);

                        $categories = json_decode($row[8], true) ?: [];
                        $tags = json_decode($row[9], true) ?: [];
                        $featuredImageFile = trim($row[10]);
                        $galleryFiles = json_decode($row[11], true) ?: [];
                        $healthConditions = json_decode($row[12], true) ?: [];
                        $variations = json_decode($row[13], true) ?: [];
                        // dd(json_decode($row[11]));
                        $baseUrl = public_path('medishop_img');
                        // Log::info($baseUrl . '/' . $featuredImageFile);
                        if ($featuredImageFile) {
                            $product->addMedia($baseUrl . '/' . $featuredImageFile)->preservingOriginal()->toMediaCollection(Product::PRODUCT_FEATURE);
                        }

                        foreach ($galleryFiles as $file) {
                            $product->addMedia($baseUrl . '/' . trim($file))->preservingOriginal()->toMediaCollection(Product::PRODUCT_GALLERY);
                        }

                        if (!empty($categories)) {
                            $product->categories()->attach($categories);
                        }

                        if (!empty($tags)) {
                            $product->tags()->attach($tags);
                        }

                        if (!empty($healthConditions)) {
                            $product->healthConditions()->attach($healthConditions);
                        }

                        if (!empty($variations)) {
                            foreach ($variations as $variation) {
                                // dd($variation);
                                $variation_data = [
                                    "name" => $variation['name'],
                                    "size_value" => $variation['size_value'],
                                    "size_unit" => $variation['size_unit'],
                                    "platform_price" => $variation['platform_price'],
                                ];
                                $product_variation = $product->variations()
                                    ->create($variation_data);
                                // dd($variation);
                                $vendor_price = [
                                    "units_in_stock" => $variation['units_in_stock'],
                                    "batch_number" => $variation['batch_number'],
                                    "manufacture" => $variation['manufacture'],
                                    "expiry_date" => $variation['expiry_date'],
                                    'product_variation_id' => $product_variation->id,
                                    "price" => $variation['platform_price']
                                ];
                                // Log::info($vendor_price);
                                $product->productVendors()->create([
                                    'status' => true,
                                    'is_approved' => true,
                                    'vendor_id' => Auth::user()->vendor->id
                                ])
                                    ->vendorPrices()
                                    ->create($vendor_price);
                            }
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            Log::info($e);
            $total_bulk_upload_errors_count++;
        }

        return $this->apiSuccess('Bulk upload completed with ' . $total_bulk_upload_errors_count . ' errors');
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/bulk-upload/tag",
     *     summary="Bulk upload a product tag.",
     *     description="Bulk upload a product tag.",
     *     operationId="BulkTagUpload",
     *     tags={"BulkUpload"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Upload file",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="file file to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag create response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tag added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function tagBulkUpload(Request $request) {
        $data = $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv'
        ]);
        $tag_data = $this->getExcelRowInArray($data['file'], false);
        try {
            $tag_data = array_map(fn($item) => ['status' => true, 'name' => $item[0]], $tag_data);
            // Log::info($tag_data);
            DB::table('tags')->delete();
            foreach ($tag_data as $tag) {
                Tag::create($tag);
            }
        } catch (\Exception $e) {
            Log::info($e);
        }
        return $this->apiSuccess('Tag upload completed.');
    }


    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/bulk-upload/category",
     *     summary="Store a product category",
     *     description="Store a product category.",
     *     operationId="BulkCategoryUpload",
     *     tags={"BulkUpload"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Upload file",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="file file to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag bulk upload response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="'Tag upload completed.'"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
    */
    function categoryBulkUpload(Request $request)
    {

        $data = $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv'
        ]);
        $chunked_data = $this->getExcelRowInArray($data['file']);
        $total_bulk_upload_errors_count = 0;
        try {
            // Log::info("variations", $rows);
            DB::transaction(function () use ($chunked_data) {
                $categories = Category::all();

                foreach ($categories as $category) {
                    $category->clearMediaCollection();
                    $category->delete();
                }
                
                foreach ($chunked_data as $chunk) {
                    foreach ($chunk as $key => $row) {
                        $categoryData = [
                            'menu_order' => ++$key,
                            'status' => true,
                            'name' => $row[0],
                            'discount_percent' => $row[1]
                        ];
                        $baseUrl = public_path('medishop_img/category');
                        Category::create($categoryData)
                            ->addMedia($baseUrl . '/' . $row[2])
                            ->preservingOriginal()
                            ->toMediaCollection(Category::CATEGORY_IMAGE);
                    }
                }
            });
        } catch (\Exception $e) {
            Log::info($e);
            $total_bulk_upload_errors_count++;
        }

        return $this->apiSuccess('Bulk upload completed with ' . $total_bulk_upload_errors_count . ' errors');
    }
}
