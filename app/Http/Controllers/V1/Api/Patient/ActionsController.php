<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\LikeItem;
use App\Models\Patient;
use App\Models\Pharmacy;
use App\Models\Professional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActionsController extends Controller
{
    /**
     * Toggle like state for a morphable entity (Professional, Center, Pharmacy, etc.).
     *
     * **Endpoint:** `POST /api/v1/patient/like/toggle` or `POST /api/v1/patient/toggle-like`
     */
    public function toggleLike(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'likeable_type' => ['required', 'string'],
            'likeable_id' => ['required', 'integer', 'min:1'],
        ]);

        $modelClass = match (strtolower($validated['likeable_type'])) {
            'professional', 'doctor' => Professional::class,
            'center' => Center::class,
            'pharmacy', 'pharmacist' => Pharmacy::class,
            'patient' => Patient::class,
            default => $validated['likeable_type'],
        };

        if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return response()->json([
                'message' => 'Invalid or unsupported likeable model type: ' . $validated['likeable_type'],
            ], 422);
        }

        /** @var Model|null $likeable */
        $likeable = $modelClass::find($validated['likeable_id']);

        if (! $likeable) {
            return response()->json([
                'message' => 'Target record not found.',
            ], 404);
        }

        $user = $request->user();

        $like = LikeItem::where('user_id', $user->id)
            ->where('likeable_type', get_class($likeable))
            ->where('likeable_id', $likeable->id)
            ->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
            $message = 'Unliked successfully.';
        } else {
            LikeItem::create([
                'user_id' => $user->id,
                'likeable_type' => get_class($likeable),
                'likeable_id' => $likeable->id,
            ]);
            $isLiked = true;
            $message = 'Liked successfully.';
        }

        $likesCount = LikeItem::where('likeable_type', get_class($likeable))
            ->where('likeable_id', $likeable->id)
            ->count();

        return response()->json([
            'message' => $message,
            'is_liked' => $isLiked,
            'likes_count' => $likesCount,
            'likeable_type' => $validated['likeable_type'],
            'likeable_id' => $likeable->id,
        ]);
    }
}
