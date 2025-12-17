<?php
// api/common.php

// Disable error display to prevent HTML output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Set content type to JSON
header('Content-Type: application/json; charset=utf-8');

// Custom Error Handler
function apiErrorHandler($errno, $errstr, $errfile, $errline)
{
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal Server Error',
        'error' => $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ]);
    exit();
}

// Custom Exception Handler
function apiExceptionHandler($exception)
{
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Exception Occurred',
        'error' => $exception->getMessage(),
        'file' => basename($exception->getFile()),
        'line' => $exception->getLine()
    ]);
    exit();
}

set_error_handler("apiErrorHandler");
set_exception_handler("apiExceptionHandler");

// Handle CORS if needed
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>