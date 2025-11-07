<?php

namespace App\Http\Controllers\Api\V1\Client\Address;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAddressController extends Controller
{
    //
    use ResponseTrait;
    /**
     * @OA\Post(
     *     path="/user/address",
     *     summary="Store a new address for the authenticated user",
     *     description="Allows a logged-in user to add a new address with optional latitude and longitude.",
     *     tags={"User Address"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address"},
     *             @OA\Property(property="address", type="string", example="Kathmandu, Nepal"),
     *             @OA\Property(property="latitude", type="string", example="27.7172"),
     *             @OA\Property(property="longitude", type="string", example="85.3240")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Address stored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Address stored successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=5),
     *                 @OA\Property(property="address", type="string", example="Kathmandu, Nepal"),
     *                 @OA\Property(property="latitude", type="string", example="27.7172"),
     *                 @OA\Property(property="longitude", type="string", example="85.3240")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The address field is required.")
     *         )
     *     )
     * )
     */
    function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'address' => 'required|string',
            'latitude' => 'sometimes|string|nullable',
            'longitude' => 'sometimes|string|nullable',
        ]);
        $data['user_id'] = $user->id;
        $address = Address::create($data);
        return $this->apiSuccess('Address store successfull');
    }
    /**
     * @OA\Get(
     *     path="/user/address",
     *     summary="Get all addresses of the authenticated user",
     *     description="Retrieve a list of all addresses associated with the logged-in user.",
     *     tags={"User Address"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user addresses",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User address"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=3),
     *                     @OA\Property(property="address", type="string", example="Kathmandu, Nepal"),
     *                     @OA\Property(property="latitude", type="string", example="27.7172"),
     *                     @OA\Property(property="longitude", type="string", example="85.3240")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    function index()
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->get();
        return $this->apiSuccess('User address', $address);
    }
    /**
     * @OA\Put(
     *     path="/user/address/{id}",
     *     summary="Update an existing address",
     *     description="Update the user's address using its ID. Only the owner can update their address.",
     *     tags={"User Address"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Address ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address"},
     *             @OA\Property(property="address", type="string", example="Bhaktapur, Nepal"),
     *             @OA\Property(property="latitude", type="string", example="27.6710"),
     *             @OA\Property(property="longitude", type="string", example="85.4298")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Address updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Address Update Successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Address not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    function update(Address $address, Request $request)
    {
        $data = $request->validate([
            'address' => 'required|string',
            'latitude' => 'sometimes|string|nullable',
            'longitude' => 'sometimes|string|nullable',
        ]);
        $address->update($data);
        return $this->apiSuccess('Address Update Successful');
    }
    /**
     * @OA\Delete(
     *     path="/user/address/{id}",
     *     summary="Delete a user address",
     *     description="Delete a specific address by ID. Only the owner of the address can delete it.",
     *     tags={"User Address"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Address ID",
     *         @OA\Schema(type="integer", example=7)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Address deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Address Successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Address not found"
     *     )
     * )
     */
    function destroy(Address $address)
    {
        $address->delete();
        return $this->apiSuccess('Address Successful');
    }
}
