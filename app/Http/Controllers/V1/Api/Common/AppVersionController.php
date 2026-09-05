<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    /**
     * Check if the client application requires or recommends an update.
     */
    public function check(Request $request): JsonResponse
    {
        $platform = strtolower((string) $request->query('platform', 'android'));
        if (! in_array($platform, ['android', 'ios'], true)) {
            $platform = 'android';
        }

        $clientVersion = (string) $request->query('version', '1.0.0');
        $platformConfig = config("mobile.{$platform}", []);

        $minVersion = (string) ($platformConfig['min_version'] ?? '1.0.0');
        $latestVersion = (string) ($platformConfig['latest_version'] ?? '1.0.0');
        $storeUrl = (string) ($platformConfig['store_url'] ?? '');

        $messages = config('mobile.messages', []);

        $isForceUpdate = version_compare($clientVersion, $minVersion, '<');
        $isOptionalUpdate = ! $isForceUpdate && version_compare($clientVersion, $latestVersion, '<');

        if ($isForceUpdate) {
            return response()->json([
                'update_required' => true,
                'force_update' => true,
                'current_version' => $clientVersion,
                'min_version' => $minVersion,
                'latest_version' => $latestVersion,
                'title' => $messages['force_title'] ?? 'تحديث إجباري متوفر',
                'message' => $messages['force_message'] ?? 'يرجى التحديث إلى أحدث إصدار للمتابعة.',
                'button_text' => $messages['button_text'] ?? 'تحديث الآن',
                'store_url' => $storeUrl,
            ]);
        }

        if ($isOptionalUpdate) {
            return response()->json([
                'update_required' => true,
                'force_update' => false,
                'current_version' => $clientVersion,
                'min_version' => $minVersion,
                'latest_version' => $latestVersion,
                'title' => $messages['optional_title'] ?? 'تحديث جديد متوفر',
                'message' => $messages['optional_message'] ?? 'يتوفر إصدار أحدث من التطبيق.',
                'button_text' => $messages['button_text'] ?? 'تحديث الآن',
                'store_url' => $storeUrl,
            ]);
        }

        return response()->json([
            'update_required' => false,
            'force_update' => false,
            'current_version' => $clientVersion,
            'min_version' => $minVersion,
            'latest_version' => $latestVersion,
            'title' => null,
            'message' => null,
            'button_text' => null,
            'store_url' => $storeUrl,
        ]);
    }
}
