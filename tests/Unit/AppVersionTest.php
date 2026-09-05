<?php

namespace Tests\Unit;

use App\Http\Controllers\V1\Api\Common\AppVersionController;
use Illuminate\Http\Request;
use Tests\TestCase;

class AppVersionTest extends TestCase
{
    public function test_app_version_returns_no_update_when_version_meets_requirements(): void
    {
        config()->set('mobile.android.min_version', '1.0.0');
        config()->set('mobile.android.latest_version', '1.0.0');
        config()->set('mobile.android.store_url', 'https://play.google.com/store/apps/details?id=com.rendee.app');

        $controller = new AppVersionController();
        $request = Request::create('/api/v1/app/version-check', 'GET', [
            'platform' => 'android',
            'version' => '1.0.0',
        ]);

        $response = $controller->check($request);
        $data = $response->getData(true);

        $this->assertFalse($data['update_required']);
        $this->assertFalse($data['force_update']);
        $this->assertEquals('1.0.0', $data['current_version']);
    }

    public function test_app_version_returns_force_update_when_version_below_min(): void
    {
        config()->set('mobile.android.min_version', '1.1.0');
        config()->set('mobile.android.latest_version', '1.2.0');
        config()->set('mobile.android.store_url', 'https://play.google.com/store/apps/details?id=com.rendee.app');

        $controller = new AppVersionController();
        $request = Request::create('/api/v1/app/version-check', 'GET', [
            'platform' => 'android',
            'version' => '1.0.0',
        ]);

        $response = $controller->check($request);
        $data = $response->getData(true);

        $this->assertTrue($data['update_required']);
        $this->assertTrue($data['force_update']);
        $this->assertEquals('1.1.0', $data['min_version']);
        $this->assertEquals('https://play.google.com/store/apps/details?id=com.rendee.app', $data['store_url']);
    }

    public function test_app_version_returns_optional_update_when_version_below_latest(): void
    {
        config()->set('mobile.android.min_version', '1.0.0');
        config()->set('mobile.android.latest_version', '1.2.0');

        $controller = new AppVersionController();
        $request = Request::create('/api/v1/app/version-check', 'GET', [
            'platform' => 'android',
            'version' => '1.1.0',
        ]);

        $response = $controller->check($request);
        $data = $response->getData(true);

        $this->assertTrue($data['update_required']);
        $this->assertFalse($data['force_update']);
    }
}
