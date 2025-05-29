<?php
require_once('JwtHandler.php');

$inData = getRequestInfo();

$login = $inData["login"];
$password = $inData["password"];

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $stmt = $conn->prepare("SELECT ID, FirstName, LastName FROM Users WHERE Login=? AND Password=?");
    $stmt->bind_param("ss", $login, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $jwt = new JwtHandler();
        $token = $jwt->generateToken($row["ID"]);

        sendResultInfoAsJson(json_encode(array(
            "id" => $row["ID"],
            "firstName" => $row["FirstName"],
            "lastName" => $row["LastName"],
            "token" => $token,
            "error" => ""
        )));
    } else {
        returnWithError("No Records Found");
    }

    $stmt->close();
    $conn->close();
}

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj)
{
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err)
{
    $retValue = array("id" => 0, "firstName" => "", "lastName" => "", "token" => "", "error" => $err);
    sendResultInfoAsJson(json_encode($retValue));
}
?>
