<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('http://laraccess.test/api/admin/usuarios', 'GET');
$request->headers->set('Accept', 'application/json');
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
echo substr($response->getContent(), 0, 100);

