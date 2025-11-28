<?php

namespace App\Http\Controllers\Api\V1\BulkUpload;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HealthCondition;
use App\Models\Product;
use App\Models\Product\GenericProductName;
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
    private string $base_url = '';

    public function __construct()
    {
        $this->base_url = public_path('medishop_img');
    }

    private function getExcelRowInArray(UploadedFile $file, bool $in_chunk = true) {
        $spreadsheet = IOFactory::load($file->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();
        Log::info($rows[0]);
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
     *         description="Bulk upload success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Bulk upload completed with 0 errors"),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
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
                            'is_featured' => filter_var($row[0], FILTER_VALIDATE_BOOLEAN),
                            'brand_id' => $row[1],
                            'generic_product_name_id' => $row[2],
                            'name' => $row[3],
                            'description' => $row[4],
                            'discount_percent' => is_numeric($row[5]) ? (float)$row[5] : null,
                            'prescription_required' => filter_var($row[6], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                            'added_by' => Auth::id()
                        ];

                        $product = Product::create($productData);

                        $categories = json_decode($row[7], true) ?: [];
                        $tags = json_decode($row[8], true) ?: [];
                        $featuredImageFile = trim($row[9]);
                        $galleryFiles = json_decode($row[10], true) ?: [];
                        $healthConditions = json_decode($row[11], true) ?: [];
                        $variations = json_decode($row[12], true) ?: [];
                        // dd(json_decode($row[11]));
                        $baseUrl = $this->base_url.'/product';
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
                            $product_vendor = $product->productVendors()->create([
                                'status' => true,
                                'is_approved' => true,
                                'vendor_id' => Auth::user()->vendor->id
                            ]);
                            foreach ($variations as $variation) {
                                // dd($variation);
                                $variation_data = [
                                    "name" => $variation['name'],
                                    "size_value" => $variation['size_value'],
                                    "size_unit" => $variation['size_unit'],
                                    "platform_price" => $variation['platform_price'],
                                ];
                                $product_variation = $product->variations()->create($variation_data);
                                $vendor_price = [
                                    "units_in_stock" => $variation['units_in_stock'],
                                    "batch_number" => $variation['batch_number'],
                                    "manufacture" => $variation['manufacture'],
                                    "expiry_date" => $variation['expiry_date'],
                                    'product_variation_id' => $product_variation->id,
                                    "price" => $variation['platform_price']
                                ];
                                $product_vendor->vendorPrices()->create($vendor_price);

                            }
                            // Log::info($vendor_price);
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
            // Log::info($tag_data);
            DB::transaction(function() use($tag_data){
                $tag_data = array_map(fn($item) => ['status' => true, 'name' => $item[0]], $tag_data);
                foreach ($tag_data as $tag) {
                    Tag::create($tag);
                }
            });
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
     *         description="Bulk upload success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Bulk upload completed with 0 errors"),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
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
                foreach ($chunked_data as $chunk) {
                    foreach ($chunk as $key => $row) {
                        $categoryData = [
                            'menu_order' => ++$key,
                            'status' => true,
                            'name' => $row[0],
                            'discount_percent' => $row[1]
                        ];
                        $baseUrl = $this->base_url. '/category';
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

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/bulk-upload/generic-product-name",
     *     summary="Bulk upload a product generic name.",
     *     description="Bulk upload a product generic name.",
     *     operationId="BulkProductGenericNameUpload",
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
     *             @OA\Property(property="message", type="string", example="Generic product name upload completed."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function genericProductNameBulkUpload(Request $request) {

        $data = $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv'
        ]);
        $generic_product_name = $this->getExcelRowInArray($data['file'], false);
        try {
            DB::transaction(function () use($generic_product_name){                
                $generic_product_names = array_map(fn($item) => ['status' => true, 'name' => $item[0]], $generic_product_name);
                foreach ($generic_product_names as $generic_product_name) {
                    GenericProductName::create($generic_product_name);
                }
            });
        } catch (\Exception $e) {
            Log::info($e);
        }
        return $this->apiSuccess('Generic product name upload completed.');
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/bulk-upload/brand",
     *     summary="Store a product brand",
     *     description="Store a product brand.",
     *     operationId="BulkBrandUpload",
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
     *         description="Bulk upload success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Bulk upload completed with 0 errors"),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function brandBulkUpload(Request $request) {
        $data = $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv'
        ]);
        $chunked_data = $this->getExcelRowInArray($data['file']);
        $total_bulk_upload_errors_count = 0;
        try {
            DB::transaction(function () use ($chunked_data) {
                foreach ($chunked_data as $chunk) {
                    foreach ($chunk as $row) {
                        $brandData = [
                            'status' => true,
                            'name' => $row[0],
                            'is_featured' => filter_var($row[1], FILTER_VALIDATE_BOOLEAN),
                            'is_popular' => filter_var($row[2], FILTER_VALIDATE_BOOLEAN)
                        ];
                        $baseUrl = $this->base_url. '/brand';
                        Brand::create($brandData)
                            ->addMedia($baseUrl . '/' . $row[3])
                            ->preservingOriginal()
                            ->toMediaCollection(Brand::BRAND_IMAGE);
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
     *     path="/admin/bulk-upload/health-condition",
     *     summary="Store a product health condition",
     *     description="Store a product health condition.",
     *     operationId="BulkHealthConditionUpload",
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
     *         description="Bulk upload success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Bulk upload completed with 0 errors"),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function healthConditionBulkUpload(Request $request) {
        $data = $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv'
        ]);
        $chunked_data = $this->getExcelRowInArray($data['file']);
        $total_bulk_upload_errors_count = 0;
        try {
            DB::transaction(function () use ($chunked_data) {
                foreach ($chunked_data as $chunk) {
                    foreach ($chunk as $row) {
                        $health_condition_image = [
                            'status' => true,
                            'name' => $row[0],
                            'status' => filter_var($row[1], FILTER_VALIDATE_BOOLEAN),
                            'description' => $row[2]
                        ];
                        $baseUrl = $this->base_url.'/health_condition';
                        HealthCondition::create($health_condition_image)
                            ->addMedia($baseUrl . '/' . $row[3])
                            ->preservingOriginal()
                            ->toMediaCollection(HealthCondition::HEALTH_CONDITION_IMAGE);
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
