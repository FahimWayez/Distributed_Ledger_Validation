<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["clientAuthenticated"]) || $_SESSION["clientAuthenticated"] !== true) {
    header("location: index.html");
    exit();
}

include 'dbConnection.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $storedUserID = $_SESSION["userID"];
    // $storedPassword = $_SESSION["password"];
    $receiverUserID = $_POST["receiver"];
    $amount = $_POST["amount"];
    $current_time = $_POST["current_time"];
    $taka = $_POST["taka"];


        
    if($receiverUserID == 1 || $receiverUserID == 2 || $receiverUserID ==3)
    {
        echo "Receiver with the given userID does not exist.";
        $conn->close();
        header("Refresh: 3; url=clientDashboard.php");
        exit;
    }
    else
    {
        $conn = dbConnection();
        $checkReceiverQuery = "SELECT userID FROM loginCredentials WHERE userID = '$receiverUserID'";
        $receiverResult = $conn->query($checkReceiverQuery);
        
        if ($receiverResult->num_rows === 0) {
            echo "Receiver with the given userID does not exist.";
            $conn->close();
            header("Refresh: 3; url=clientDashboard.php");
            exit;
        }
    }
    
    $leftOver = $taka - $amount;
    
    $conn2 = dbConnection();
    $updateTakaQuery = "UPDATE loginCredentials SET taka = '$leftOver' WHERE userID = '$storedUserID'";
    // $sqlPassword = "SELECT loginCredentials SET taka = '$leftOver' WHERE userID = '$storedUserID'";
    $updateResult = $conn2->query($updateTakaQuery);
    
    if (!$updateResult) {
        echo "Error updating balance.";
        header("Refresh: 3; url=clientDashboard.php");
        exit;
    }

    $requests = json_decode(file_get_contents("pendingRequest.json"), true);
    $currentTimestamp = time();

    foreach ($requests as $key => $request) {
        $requestTimestamp = $request["timestamp"];
        if (($currentTimestamp - $requestTimestamp) >= 3600) {
            unset($requests[$key]);
        }
    }
    $requestId = uniqid();
    
    $encryptedReceiver = openssl_encrypt($receiverUserID, "aes-256-cbc", 3, 0, "}^(*^s83&ADP{>kd");
    $encryptedAmount = openssl_encrypt($amount, "aes-256-cbc", 3, 0, "}^(*^s83&ADP{>kd");
    $encryptedTaka = openssl_encrypt($leftOver, "aes-256-cbc", 3, 0, "}^(*^s83&ADP{>kd");
        
    $requestData = array(
        "requestId" => $requestId,
        "sender" => $storedUserID,
        "receiver" => $encryptedReceiver,
        "amount" => $encryptedAmount,
        "leftover" => $encryptedTaka,
        "voteCount" => 0,
        "timestamp" => $current_time,
        "approved" => false,
        "approvedBy" => []
    );
    
    // $existingData = file_get_contents("pendingRequest.json");
    // $requests = json_decode($existingData, true);
    
    $requests[] = $requestData;
    
    $jsonData = json_encode($requests, JSON_PRETTY_PRINT);
    if (file_put_contents("pendingRequest.json", $jsonData)) {
        echo "Your request has been added to the pending list. Redirecting you to the previous page...";
        echo "Current balance: " . $leftOver;
        header("Refresh: 3; url=clientDashboard.php");
        exit;
    } else {
        echo "Error storing form data.";
        header("Refresh: 3; url=clientDashboard.php");
        exit;
    }
}

?>