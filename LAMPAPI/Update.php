<?php
    // UpdateContact.php

    $inData = getRequestInfo();

    $contactId = $inData["id"] ?? null; 
    $userId = $inData["userId"] ?? null;
    $firstName = $inData["firstName"] ?? null;
    $lastName = $inData["lastName"] ?? null;
    $phone = $inData["phone"] ?? null;
    $email = $inData["email"] ?? null;

    if (is_null($contactId) || is_null($userId)) {
        returnWithError("Contact ID and User ID are required.");
        exit();
    }

    if (is_null($firstName) && is_null($lastName) && is_null($phone) && is_null($email)) {
        returnWithError("No fields provided to update.");
        exit();
    }

    $conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
    if ($conn->connect_error) {
        returnWithError($conn->connect_error);
        exit();
    }

    $stmt_check = $conn->prepare("SELECT ID FROM Contacts WHERE ID = ? AND UserID = ?");
    $stmt_check->bind_param("ii", $contactId, $userId);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows == 0) {
        $stmt_check->close();
        $conn->close();
        returnWithError("Contact not found or you do not have permission to update it.");
        exit();
    }
    $stmt_check->close();

    $setClauses = [];
    $params = [];
    $types = "";

    if (!is_null($firstName)) { $setClauses[] = "FirstName = ?"; $params[] = $firstName; $types .= "s"; }
    if (!is_null($lastName))  { $setClauses[] = "LastName = ?";  $params[] = $lastName;  $types .= "s"; }
    if (!is_null($phone))     { $setClauses[] = "Phone = ?";     $params[] = $phone;     $types .= "s"; }
    if (!is_null($email))     { $setClauses[] = "Email = ?";     $params[] = $email;     $types .= "s"; }

    if (empty($setClauses)) {
        $conn->close();
        returnWithInfo(["message" => "No valid fields provided for update."]);
        exit();
    }

    $sql = "UPDATE Contacts SET " . implode(", ", $setClauses) . " WHERE ID = ? AND UserID = ?";
    
    $params[] = $contactId;
    $params[] = $userId;
    $types .= "ii";

    $stmt_update = $conn->prepare($sql);
    $bindNames = [$types];
    for ($i = 0; $i < count($params); $i++) {
        $bindNames[] = &$params[$i];
    }
    call_user_func_array([$stmt_update, 'bind_param'], $bindNames);

    if ($stmt_update->execute()) {
        if ($stmt_update->affected_rows > 0) {
            $stmt_get = $conn->prepare("SELECT ID, UserID, FirstName, LastName, Phone, Email FROM Contacts WHERE ID = ?");
            $stmt_get->bind_param("i", $contactId);
            $stmt_get->execute();
            $updatedContactResult = $stmt_get->get_result();
            $updatedContact = $updatedContactResult->fetch_assoc();
            $stmt_get->close();
            returnWithInfo($updatedContact);
        } else {
            returnWithInfo(["message" => "No changes made to the contact (data may be the same)."]);
        }
    } else {
        returnWithError("Failed to update contact: " . $stmt_update->error);
    }

    $stmt_update->close();
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

    function returnWithInfo($data)
    {
        if (is_array($data)) {
            $data["error"] = "";
        } else {
            $data = ["message" => $data, "error" => ""];
        }
        sendResultInfoAsJson(json_encode($data));
    }
?>