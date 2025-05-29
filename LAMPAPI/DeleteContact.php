<?php
require 'JwtHandler.php';
$jwt = new JwtHandler();

// Check for Authorization header
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "Authorization header not found"]);
    exit();
}

// Validate token
$authHeader = $headers['Authorization'];
$token = trim(str_replace("Bearer", "", $authHeader));
$decoded = $jwt->validateToken($token);

if (!$decoded) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid or expired token"]);
    exit();
}

// Get request data
$inData = json_decode(file_get_contents('php://input'), true);
$id = $inData["ID"];

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => $conn->connect_error]);
    exit();
}

// Prepare and execute delete
$stmt = $conn->prepare("CALL DeleteContact(?)");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(["error" => ""]);
$conn->close();
?>
