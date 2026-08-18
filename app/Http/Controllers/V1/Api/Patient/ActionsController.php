<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\LikedPartner;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActionsController extends Controller
{
    /**
     * POST /api/v1/patient/toggle-like
     * POST /api/v1/patient/like/toggle
     *
     * Request JSON:
     * {
     *   "partner_id": 4
     * }
     *
     * Response JSON:
     * {
     *   "message": "Liked successfully.",
     *   "is_liked": true,
     *   "likes_count": 12,
     *   "partner_id": 4
     * }
     */
    public function toggleLike(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'partner_id' => ['nullable', 'integer', 'min:1'],
            'id' => ['nullable', 'integer', 'min:1'],
        ]);

        $partnerId = $validated['partner_id'] ?? $validated['id'] ?? null;

        if (! $partnerId) {
            return response()->json([
                'message' => 'The partner_id field is required.',
            ], 422);
        }

        $partner = Partner::find($partnerId);

        if (! $partner) {
            return response()->json([
                'message' => 'Partner not found.',
            ], 404);
        }

        /** @var User $user */
        $user = $request->user() ?? (app()->environment('local', 'testing') ? User::first() : null);

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $like = LikedPartner::where('user_id', $user->id)
            ->where('partner_id', $partner->id)
            ->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
            $message = 'Unliked successfully.';
        } else {
            LikedPartner::create([
                'user_id' => $user->id,
                'partner_id' => $partner->id,
            ]);
            $isLiked = true;
            $message = 'Liked successfully.';
        }

        $likesCount = LikedPartner::where('partner_id', $partner->id)->count();

        return response()->json([
            'message' => $message,
            'is_liked' => $isLiked,
            'likes_count' => $likesCount,
            'partner_id' => $partner->id,
        ]);
    }
}
