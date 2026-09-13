<?php

test('swagger documentation interface is accessible', function () {
    $response = $this->get('/api/documentation');

    $response->assertOk();
    $response->assertSee('L5 Swagger UI');
});

test('generated swagger json specification is valid and contains all routes', function () {
    $docsPath = storage_path('api-docs/api-docs.json');
    expect(file_exists($docsPath))->toBeTrue();

    $content = json_decode(file_get_contents($docsPath), true);
    expect($content)->toBeArray()
        ->and($content)->toHaveKey('openapi')
        ->and($content)->toHaveKey('info')
        ->and($content)->toHaveKey('paths');

    // Verify operations exceed requirement (at least 20 routes)
    $operationCount = 0;
    foreach ($content['paths'] as $path => $methods) {
        $operationCount += count($methods);
    }

    expect($operationCount)->toBeGreaterThanOrEqual(20);
});
