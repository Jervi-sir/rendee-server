<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'contacts' => []]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'contact' => null]);
    }

    public function upsert(Request $request): JsonResponse
    {
        return response()->json(['success' => true]);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['success' => true]);
    }
}
