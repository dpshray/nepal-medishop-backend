<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BulkUploadController extends Controller
{
    use ResponseTrait;

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv'
        ]);

        $spreadsheet = IOFactory::load($data['file']->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();
        array_shift($rows);

        $chunked_data = array_chunk($rows, 100);
        $total_bulk_upload_errors_count = 0;
        try {
            Product::delete();
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
}
