<?php
require 'JwtHandler.php';
$jwt = new JwtHandler();

$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "Authorization header not found"]);
    exit();
}

$authHeader = $headers['Authorization'];
$token = trim(str_replace("Bearer", "", $authHeader));
$decoded = $jwt->validateToken($token);

if (!$decoded) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid or expired token"]);
    exit();
}

$inData = json_decode(file_get_contents('php://input'), true);
$firstName = $inData["FirstName"];
$lastName = $inData["LastName"];
$phone = $inData["Phone"];
$email = $inData["Email"];
$userID = $decoded->data->id;

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => $conn->connect_error]);
    exit();
}

$stmt = $conn->prepare("CALL AddContact(?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $firstName, $lastName, $phone, $email, $userID);
$stmt->execute();

echo json_encode(["error" => ""]);
$conn->close();
?>
