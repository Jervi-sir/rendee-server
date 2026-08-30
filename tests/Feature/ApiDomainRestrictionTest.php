<?php

test('allows api access on for-mobile.rendee.app host', function () {
    $response = $this->withHeaders(['Host' => 'for-mobile.rendee.app'])
        ->getJson('https://for-mobile.rendee.app/api/v1/catalogs?includes=wilayas');

    $response->assertOk();
});

test('api health endpoint returns alive status', function () {
    $response = $this->withHeaders(['Host' => 'for-mobile.rendee.app'])
        ->getJson('https://for-mobile.rendee.app/api/v1/health');

    $response->assertOk()
        ->assertJson([
            'status' => 'alive',
            'version' => 'v1',
        ]);
});

test('blocks api access from unauthorized domains like rendee.app or www.rendee.app', function () {
    $response = $this->withHeaders(['Host' => 'rendee.app'])
        ->getJson('https://rendee.app/api/v1/catalogs?includes=wilayas');

    $response->assertNotFound();

    $wwwResponse = $this->withHeaders(['Host' => 'www.rendee.app'])
        ->getJson('https://www.rendee.app/api/v1/catalogs?includes=wilayas');

    $wwwResponse->assertNotFound();
});
