<?php
require_once 'autoload.php';

use FixItMati\Models\ServiceRequest;

$model = new ServiceRequest();

echo "Testing ServiceRequest->getAll()..." . PHP_EOL;
$result = $model->getAll();

echo "Result: " . PHP_EOL;
echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
