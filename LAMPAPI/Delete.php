<?php
    // DeleteContact.php

    $inData = getRequestInfo();

    $contactId = $inData["id"] ?? null; 
    $userId = $inData["userId"] ?? null;

    if (is_null($contactId) || is_null($userId)) {
        returnWithError("Contact ID and User ID are required for deletion.");
        exit();
    }

    $conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
    if ($conn->connect_error) {
        returnWithError($conn->connect_error);
        exit();
    }
    
    $stmt = $conn->prepare("DELETE FROM Contacts WHERE ID = ? AND UserID = ?");
    $stmt->bind_param("ii", $contactId, $userId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            returnWithSuccessMessage("Contact deleted successfully.");
        } else {
            returnWithError("Contact not found or you do not have permission to delete it.");
        }
    } else {
        returnWithError("Failed to delete contact: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

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
        $retValue = '{"error":"' . $err . '"}';
        sendResultInfoAsJson($retValue);
    }

    function returnWithSuccessMessage($message)
    {
        // JS expects an empty error string for success
        $retValue = '{"error":""}'; 
        // If you wanted to include the message:
        // $retValue = '{"message":"' . $message . '", "error":""}';
        sendResultInfoAsJson($retValue);
    }
?>