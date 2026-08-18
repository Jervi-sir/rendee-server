<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportMessageController extends Controller
{
    /**
     * POST /api/v1/support-messages
     *
     * Request JSON:
     * {
     *   "subject": "Adding a new service...",
     *   "message": "Hello, I want to inquire about..."
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();

        $supportMessage = SupportMessage::create([
            'user_id' => $user?->id,
            'user_role_code' => $user?->user_role_code,
            'name' => $validated['name'] ?? $user?->full_name ?? $user?->name,
            'email' => $validated['email'] ?? $user?->email,
            'phone' => $validated['phone'] ?? $user?->phone_number,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم استلام رسالتك بنجاح وسيتواصل معك فريق الدعم قريباً.',
            'data' => [
                'id' => $supportMessage->id,
                'subject' => $supportMessage->subject,
                'status' => $supportMessage->status,
                'created_at' => $supportMessage->created_at?->toIso8601String(),
            ],
        ], 201);
    }
}
