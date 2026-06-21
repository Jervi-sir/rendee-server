<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Center\M4;

use App\Http\Controllers\Controller;
use App\Models\Center;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class M4CenterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $center = null;

        if ($user) {
            $center = Center::where('user_id', $user->id)->with('catalog')->first();
        }

        if (!$center) {
            $center = Center::with('catalog')->first();
        }

        if (!$center) {
            return response()->json([
                'profile' => [
                    'name' => 'المركز الطبي',
                    'type' => 'مركز طبي',
                ],
            ]);
        }

        $typeLabel = $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي';

        return response()->json([
            'profile' => [
                'name' => $center->name ?? 'المركز الطبي',
                'type' => $typeLabel,
            ],
        ]);
    }
}
