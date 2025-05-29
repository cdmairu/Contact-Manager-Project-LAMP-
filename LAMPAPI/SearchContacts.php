<?php
// Example: Protected SearchContacts.php

require 'JwtHandler.php';

$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    echo json_encode(['error' => 'Authorization header missing']);
    http_response_code(401);
    exit();
}

$authHeader = $headers['Authorization'];
$matches = [];
if (!preg_match('/Bearer\s(.*)/', $authHeader, $matches)) {
    echo json_encode(['error' => 'Invalid Authorization header format']);
    http_response_code(401);
    exit();
}

$jwt = $matches[1];
$jwtHandler = new JwtHandler();
$decoded = $jwtHandler->validateToken($jwt);

if (!$decoded['valid']) {
    echo json_encode(['error' => $decoded['error']]);
    http_response_code(401);
    exit();
}

$inData = json_decode(file_get_contents('php://input'), true);
$userId = $inData['userId'];
$search = $inData['search'];

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $stmt = $conn->prepare("CALL SearchContacts(?, ?)");
    $stmt->bind_param("is", $userId, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $searchResults = "";
    $searchCount = 0;

    while ($row = $result->fetch_assoc()) {
        if ($searchCount > 0) {
            $searchResults .= ",";
        }
        $searchCount++;
        $searchResults .= json_encode($row);
    }

    echo "{\"results\":[" . $searchResults . "],\"error\":\"\"}";
    $stmt->close();
    $conn->close();
}

function returnWithError($err)
{
    echo json_encode(['results' => [], 'error' => $err]);
}
