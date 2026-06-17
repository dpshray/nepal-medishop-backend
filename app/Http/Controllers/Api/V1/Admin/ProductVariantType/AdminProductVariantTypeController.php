<?php

namespace App\Http\Controllers\Api\V1\Admin\ProductVariantType;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductVariantType\AdminProductVariantTypeRequest;
use App\Http\Resources\Admin\ProductVariantType\AdminProductVariantTypeResource;
use App\Models\ProductVariantType\FormType;
use App\Models\ProductVariantType\PackageType;
use App\Models\ProductVariantType\UnitType;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AdminProductVariantTypeController extends Controller
{
    //
    use PaginationTrait, ResponseTrait;
    /**
     * @OA\Get(
     *     path="/admin/product-variant-types",
     *     tags={"Product Variant Type"},
     *     summary="Form Type List",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Form Type List"
     *     )
     * )
     */
    function index(Request $request)
    {
        $per_page = $request->input('per_page', 1000);
        $search = $request->input('search');
        $form_type = FormType::with(['packageTypes.unitTypes'])->when($search, function ($query) use ($search) {
            return $query->where('name', 'like', '%' . $search . '%');
        })->orderBy('id', 'desc')->paginate($per_page);
        $data = $this->makePaginationResponse($form_type, fn($items) => AdminProductVariantTypeResource::collection($items))->data;
        return $this->apiSuccess('Form Type List', $data);
    }
    /**
     * @OA\Get(
     *     path="/admin/product-variant-types/{formType}",
     *     tags={"Product Variant Type"},
     *     summary="Form Type Detail",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="formType",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string",example="form-type-uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Form Type Detail",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="uuid", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *
     *                 @OA\Property(
     *                     property="package_types",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="uuid", type="string"),
     *                         @OA\Property(property="name", type="string"),
     *
     *                         @OA\Property(
     *                             property="unit_types",
     *                             type="array",
     *
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer"),
     *                                 @OA\Property(property="uuid", type="string"),
     *                                 @OA\Property(property="name", type="string")
     *                             )
     *                         )
     *                     )
     *                 ),
     *
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="updated_at", type="string")
     *             )
     *         )
     *     )
     * )
     */
    function show($uuid)
    {
        $formType = FormType::where('uuid', $uuid)->first();
        $formType->loadMissing(['packageTypes.unitTypes']);
        $data = new AdminProductVariantTypeResource($formType);
        return $this->apiSuccess('Form Type Detail', $data);
    }
    /**
     * @OA\Post(
     *     path="/admin/product-variant-types",
     *     tags={"Product Variant Type"},
     *     summary="Create Form Type",
     *     security={{"sanctum": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","package_types"},
     *
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Tablet"
     *             ),
     *
     *             @OA\Property(
     *                 property="package_types",
     *                 type="array",
     *
     *                 @OA\Items(
     *                     type="object",
     *                     required={"name","unit_types"},
     *
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="Box"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="unit_types",
     *                         type="array",
     *
     *                         @OA\Items(
     *                             type="object",
     *                             required={"name"},
     *
     *                             @OA\Property(
     *                                 property="name",
     *                                 type="string",
     *                                 example="10 Tablets"
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Form Type Created Successfully"
     *     )
     * )
     */
    function store(AdminProductVariantTypeRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $form_type = FormType::create(Arr::except($data, ['package_types']));

            if (!empty($data['package_types'])) {
                foreach ($data['package_types'] as $package_type_data) {
                    $package_type = PackageType::create([
                        'name' => $package_type_data['name'],
                        'form_type_id' => $form_type->id,
                    ]);

                    if (!empty($package_type_data['unit_types'])) {
                        foreach ($package_type_data['unit_types'] as $unit_type_data) {
                            UnitType::create([
                                'name' => $unit_type_data['name'],
                                'package_type_id' => $package_type->id,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return $this->apiSuccess('Form Type Created Successfully');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->apiError('Failed to create form type: ' . $exception->getMessage());
        }
    }
    /**
     * @OA\Put(
     *     path="/admin/product-variant-types/{formType}",
     *     tags={"Product Variant Type"},
     *     summary="Update Form Type",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="formType",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string",example="form-type-uuid")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Capsule"
     *             ),
     *
     *             @OA\Property(
     *                 property="package_types",
     *                 type="array",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(
     *                         property="uuid",
     *                         type="string",
     *                         example="package-type-uuid"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="Bottle"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="unit_types",
     *                         type="array",
     *
     *                         @OA\Items(
     *                             type="object",
     *
     *                             @OA\Property(
     *                                 property="uuid",
     *                                 type="string",
     *                                 example="unit-type-uuid"
     *                             ),
     *
     *                             @OA\Property(
     *                                 property="name",
     *                                 type="string",
     *                                 example="30 Capsules"
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Form Type Updated Successfully"
     *     )
     * )
     */
    function update(AdminProductVariantTypeRequest $request, $uuid)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            $formType = FormType::where('uuid', $uuid)->first();
            $formType->update(Arr::except($data, ['package_types']));

            if (!empty($data['package_types'])) {
                foreach ($data['package_types'] as $package_type_data) {
                    if (!empty($package_type_data['uuid'])) {
                        $package_type = PackageType::where('uuid', $package_type_data['uuid'])
                            ->where('form_type_id', $formType->id)
                            ->first();

                        if (!$package_type) {
                            continue; // id sent but doesn't belong to this form type — skip
                        }

                        $package_type->update([
                            'name' => $package_type_data['name'] ?? $package_type->name,
                        ]);
                    } else {
                        $package_type = PackageType::create([
                            'name'         => $package_type_data['name'],
                            'form_type_id' => $formType->id,
                        ]);
                    }

                    if (!empty($package_type_data['unit_types'])) {
                        foreach ($package_type_data['unit_types'] as $unit_type_data) {
                            if (!empty($unit_type_data['uuid'])) {
                                $unit_type = UnitType::where('uuid', $unit_type_data['uuid'])
                                    ->where('package_type_id', $package_type->id)
                                    ->first();

                                if (!$unit_type) {
                                    continue;
                                }

                                $unit_type->update([
                                    'name' => $unit_type_data['name'] ?? $unit_type->name,
                                ]);
                            } else {
                                UnitType::create([
                                    'name'            => $unit_type_data['name'],
                                    'package_type_id' => $package_type->id,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return $this->apiSuccess('Form Type Updated Successfully');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->apiError('Failed to update form type: ' . $exception->getMessage());
        }
    }
    /**
     * @OA\Delete(
     *     path="/admin/product-variant-types/{formType}",
     *     tags={"Product Variant Type"},
     *     summary="Delete Form Type",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="formType",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string",example="form-type-uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Form Type Deleted Successfully"
     *     )
     * )
     */
    function destroy($uuid)
    {
        DB::beginTransaction();
        try {
            $formType = FormType::where('uuid', $uuid)->first();
            $formType->delete();
            DB::commit();
            return $this->apiSuccess('Form Type Deleted Successfully');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->apiError('Failed to delete form type: ' . $exception->getMessage());
        }
    }
    /**
     * @OA\Delete(
     *     path="/admin/package-types/{uuid}",
     *     tags={"Product Variant Type"},
     *     summary="Delete Package Type",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string",example="package-type-uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Package Type Deleted Successfully"
     *     )
     * )
     */
    function deletepackagetype($uuid)
    {
        $package_type = PackageType::where('uuid', $uuid)->first();
        $package_type->delete();
        return $this->apiSuccess('Package Type Deleted Successfully');
    }
    /**
     * @OA\Delete(
     *     path="/admin/unit-types/{uuid}",
     *     tags={"Product Variant Type"},
     *     summary="Delete Unit Type",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string",example="unit-type-uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unit Type Deleted Successfully"
     *     )
     * )
     */
    function deleteunit($uuid)
    {
        $unit_type = UnitType::where('uuid', $uuid)->first();
        $unit_type->delete();
        return $this->apiSuccess('Unit Type Deleted Successfully');
    }
}
