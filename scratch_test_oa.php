<?php

require 'vendor/autoload.php';

$generator = new \OpenApi\Generator();
$analyser = new \OpenApi\Analysers\ReflectionAnalyser([
    new \OpenApi\Analysers\DocBlockAnnotationFactory(),
    new \OpenApi\Analysers\AttributeAnnotationFactory(),
]);
$generator->setAnalyser($analyser);

try {
    $openapi = $generator->generate([__DIR__ . '/app/Http/Controllers/Api/V1/BaseApiController.php']);
    echo "Title: " . $openapi->info->title . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
