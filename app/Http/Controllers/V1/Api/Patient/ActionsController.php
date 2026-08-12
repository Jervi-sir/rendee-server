<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\LikeItem;
use App\Models\Partner;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActionsController extends Controller
{
    /**
     * Toggle like state for a morphable entity (Partner by default).
     *
     * **Endpoint:** `POST /api/v1/patient/like/toggle` or `POST /api/v1/patient/toggle-like`
     */
    public function toggleLike(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'likeable_type' => ['nullable', 'string'],
            'likeable_id' => ['nullable', 'integer', 'min:1'],
            'partner_id' => ['nullable', 'integer', 'min:1'],
            'id' => ['nullable', 'integer', 'min:1'],
        ]);

        $likeableId = $validated['likeable_id'] ?? $validated['partner_id'] ?? $validated['id'] ?? null;

        if (! $likeableId) {
            return response()->json([
                'message' => 'The likeable_id, partner_id, or id field is required.',
            ], 422);
        }

        $typeInput = strtolower($validated['likeable_type'] ?? 'partner');

        $modelClass = match ($typeInput) {
            'patient' => Patient::class,
            default => Partner::class,
        };

        if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return response()->json([
                'message' => 'Invalid or unsupported likeable model type: ' . $typeInput,
            ], 422);
        }

        /** @var Model|null $likeable */
        $likeable = $modelClass::find($likeableId);

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
            'likeable_type' => $typeInput,
            'likeable_id' => $likeable->id,
        ]);
    }
}
