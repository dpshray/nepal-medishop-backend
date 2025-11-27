<?php

namespace App\Http\Controllers\Api\V1\BulkUpload;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 *  @OA\Info(
 *     version="1.0",
 *     title="Medishop",
 *     description="Medishop Project"
 * )
 *
 * @OA\Server(
 *     url="https://api.medishop.dworklabs.com/api/v1",
 *     description="Live API Server"
 * )
 *
 * @OA\Server(
 *     url="http://192.168.100.23:8008/api/v1",
 *     description="Localhost API Server 1"
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000/api/v1",
 *     description="Localhost API Server 2"
 * )
 *  @OA\Server(
 *     url="http://192.168.100.18:8000/api/v1",
 *     description="Localhost API Server 3"
 * )
*/
class BulkUploadController extends Controller
{
}
