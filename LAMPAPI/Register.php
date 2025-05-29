<?php
header("Content-Type: application/json");
$inData = json_decode(file_get_contents('php://input'), true);

$firstName = $inData["firstName"];
$lastName = $inData["lastName"];
$login = $inData["login"];
$password = $inData["password"];

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $stmt = $conn->prepare("INSERT INTO Users (FirstName, LastName, Login, Password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $firstName, $lastName, $login, $password);
    
    if ($stmt->execute()) {
        echo json_encode(["error" => ""]);
    } else {
        returnWithError("User Already Exists or Invalid Input");
    }

    $stmt->close();
    $conn->close();
}

function returnWithError($err) {
    echo json_encode(["error" => $err]);
}
?>
