<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\ContactPlatform;
use App\Models\User;
use App\Models\UserContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    /**
     * List all contacts belonging to the authenticated user.
     *
     * GET /api/v1/contacts
     *
     * Response JSON:
     * {
     *   "success": true,
     *   "count": 2,
     *   "contacts": [
     *     {
     *       "id": 1,
     *       "user_id": 4,
     *       "platform_code": "whatsapp",
     *       "platform": {
     *         "code": "whatsapp",
     *         "en": "WhatsApp",
     *         "fr": "WhatsApp",
     *         "ar": "واتساب"
     *       },
     *       "url": "0550123456",
     *       "target_user_type": "doctor",
     *       "created_at": "2026-08-18T00:30:00Z",
     *       "updated_at": "2026-08-18T00:30:00Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthenticated',
            ], 401);
        }

        $contacts = $user->contacts()
            ->with('platform')
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn(UserContact $contact) => $this->formatContact($contact));

        return response()->json([
            'success' => true,
            'count' => $contacts->count(),
            'contacts' => $contacts,
        ]);
    }

    /**
     * Show a single contact item.
     *
     * GET /api/v1/contacts/{id}
     *
     * Response JSON:
     * {
     *   "success": true,
     *   "contact": {
     *     "id": 1,
     *     "user_id": 4,
     *     "platform_code": "whatsapp",
     *     "platform": {
     *       "code": "whatsapp",
     *       "en": "WhatsApp",
     *       "fr": "WhatsApp",
     *       "ar": "واتساب"
     *     },
     *     "url": "0550123456",
     *     "target_user_type": "doctor",
     *     "created_at": "2026-08-18T00:30:00Z",
     *     "updated_at": "2026-08-18T00:30:00Z"
     *   }
     * }
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthenticated',
            ], 401);
        }

        $contact = $user->contacts()
            ->with('platform')
            ->find($id);

        if (! $contact) {
            return response()->json([
                'success' => false,
                'error' => 'Contact not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'contact' => $this->formatContact($contact),
        ]);
    }

    /**
     * Create or update user contact(s).
     *
     * POST /api/v1/contacts/upsert
     *
     * Request JSON (Single):
     * {
     *   "platform_code": "whatsapp",
     *   "url": "0550123456",
     *   "target_user_type": "doctor"
     * }
     *
     * Request JSON (Batch):
     * {
     *   "contacts": [
     *     { "platform_code": "whatsapp", "url": "0550123456" },
     *     { "platform_code": "email", "url": "contact@doctor.dz" },
     *     { "platform_code": "facebook", "url": "https://facebook.com/mycabinet" }
     *   ]
     * }
     *
     * Response JSON:
     * {
     *   "success": true,
     *   "message": "Contact(s) saved successfully",
     *   "contacts": [ ... ]
     * }
     */
    public function upsert(Request $request): JsonResponse
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthenticated',
            ], 401);
        }

        // 1. Batch Payload
        if ($request->has('contacts') && is_array($request->input('contacts'))) {
            $validated = $request->validate([
                'contacts' => ['required', 'array'],
                'contacts.*.id' => ['nullable', 'integer'],
                'contacts.*.platform_code' => ['required', 'string', Rule::exists('contact_platforms', 'code')],
                'contacts.*.url' => ['required', 'string', 'max:500'],
                'contacts.*.target_user_type' => ['nullable', 'string', 'max:50'],
            ]);

            $savedContacts = [];
            foreach ($validated['contacts'] as $item) {
                $contact = null;
                if (! empty($item['id'])) {
                    $contact = $user->contacts()->find($item['id']);
                }

                if (! $contact) {
                    $contact = UserContact::firstOrNew([
                        'user_id' => $user->id,
                        'platform_code' => $item['platform_code'],
                    ]);
                }

                $contact->url = $item['url'];
                $contact->target_user_type = $item['target_user_type'] ?? $user->user_role_code;
                $contact->save();

                $savedContacts[] = $this->formatContact($contact->load('platform'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Contacts updated successfully',
                'count' => count($savedContacts),
                'contacts' => $savedContacts,
            ]);
        }

        // 2. Single Item Payload
        $validated = $request->validate([
            'id' => ['nullable', 'integer'],
            'platform_code' => ['required', 'string', Rule::exists('contact_platforms', 'code')],
            'url' => ['required', 'string', 'max:500'],
            'target_user_type' => ['nullable', 'string', 'max:50'],
        ]);

        $contact = null;
        if (! empty($validated['id'])) {
            $contact = $user->contacts()->find($validated['id']);
        }

        if (! $contact) {
            $contact = UserContact::firstOrNew([
                'user_id' => $user->id,
                'platform_code' => $validated['platform_code'],
            ]);
        }

        $contact->url = $validated['url'];
        $contact->target_user_type = $validated['target_user_type'] ?? $user->user_role_code;
        $contact->save();

        $formatted = $this->formatContact($contact->load('platform'));

        return response()->json([
            'success' => true,
            'message' => 'Contact saved successfully',
            'contact' => $formatted,
        ]);
    }

    /**
     * Delete a contact by ID.
     *
     * DELETE /api/v1/contacts/{id}
     *
     * Response JSON:
     * {
     *   "success": true,
     *   "message": "Contact deleted successfully"
     * }
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthenticated',
            ], 401);
        }

        $contact = $user->contacts()->find($id);

        if (! $contact) {
            return response()->json([
                'success' => false,
                'error' => 'Contact not found or unauthorized',
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully',
        ]);
    }

    /**
     * Explicitly format UserContact into clean API resource shape.
     *
     * @return array<string, mixed>
     */
    private function formatContact(UserContact $contact): array
    {
        return [
            'id' => $contact->id,
            'user_id' => $contact->user_id,
            'platform_code' => $contact->platform_code,
            'platform' => $contact->platform ? [
                'code' => $contact->platform->code,
                'en' => $contact->platform->en,
                'fr' => $contact->platform->fr,
                'ar' => $contact->platform->ar,
            ] : null,
            'url' => $contact->url,
            'target_user_type' => $contact->target_user_type,
            'created_at' => $contact->created_at?->toISOString(),
            'updated_at' => $contact->updated_at?->toISOString(),
        ];
    }

    /**
     * Resolve authenticated user (with fallback for dev testing).
     */
    private function resolveAuthenticatedUser(Request $request): ?User
    {
        return $request->user() ?? (app()->environment('local', 'testing') ? User::first() : null);
    }
}
