<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["adminAuthenticated"]) || $_SESSION["adminAuthenticated"] !== true) {
    header("location: admin_login.html");
    exit();
}

require_once('dbConnection.php');


$adminID = $_SESSION["userID"];

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["reqId"])) {
    $requestId = $_GET["reqId"];
    $currentData = file_get_contents("pendingRequest.json");
    $requests = json_decode($currentData, true);

    foreach ($requests as &$request) {
        if ($request["requestId"] === $requestId) {
            if (!isset($request["approvedBy"][$adminID]) || !$request["approvedBy"][$adminID]) {
                $request["voteCount"]++;
                $request["approvedBy"][$adminID] = true;
                if($request["approvedBy"][$adminID] == true)
                {                 
                    $fileName = "approvedRequest" . $adminID . ".json";
                    $approvedData = file_get_contents($fileName);
                    $approvedRequests = json_decode($approvedData, true);
                    // $approvedRequests[] = $request;
                    $approvedRequests[] = array(
                    "requestId" => $request["requestId"],
                    "sender" => $request["sender"],
                    "receiver" => $request["receiver"],
                    "amount" => $request["amount"],
                    "timestamp" => $request["timestamp"],
                    "approved" => $request["approvedBy"][$adminID]);
                    $jsonData = json_encode($approvedRequests, JSON_PRETTY_PRINT);
                    file_put_contents($fileName, $jsonData);
                }

                $jsonData = json_encode($requests, JSON_PRETTY_PRINT);
                file_put_contents("pendingRequest.json", $jsonData);

                if ($request["voteCount"] >= 2) {
                    $request["approved"] = true;
                
                    $approvedData = file_get_contents("approvedRequests.json");
                    $approvedRequests = json_decode($approvedData, true);
                    $approvedRequests[] = $request;
                    $jsonData = json_encode($approvedRequests, JSON_PRETTY_PRINT);
                    file_put_contents("approvedRequests.json", $jsonData);

                    foreach (range(1, 3) as $adminNumber) {
                        $fileNameAdmin = "approvedRequest" . $adminNumber . ".json";
                        $approvedData = file_get_contents($fileNameAdmin);
                        $approvedRequests = json_decode($approvedData, true);
                        
                        $requestExists = false;
                        foreach ($approvedRequests as $adminRequest) {
                            if ($adminRequest["requestId"] === $request["requestId"]) {
                                $requestExists = true;
                                break;
                            }
                        }
                                            
                        if (!$requestExists) {
                            $approvedRequests[] = array(
                            "requestId" => $request["requestId"],
                            "sender" => $request["sender"],
                            "receiver" => $request["receiver"],
                            "amount" => $request["amount"],
                            "timestamp" => $request["timestamp"],
                            "approved" => $request["approvedBy"][$adminID]);
                            $jsonData = json_encode($approvedRequests, JSON_PRETTY_PRINT);
                            file_put_contents($fileNameAdmin, $jsonData);
                        }
                    }   

                    unset($requests[array_search($request, $requests)]);
                    $jsonData = json_encode(array_values($requests), JSON_PRETTY_PRINT);
                    file_put_contents("pendingRequest.json", $jsonData);
                }

                echo "Request with ID $requestId has been approved. Redirecting back to admin dashboard...";
                header("Refresh: 3; url=adminDashboard.php");
                exit;
            } else {
                echo "You have already approved this request.";
                header("Refresh: 3; url=adminDashboard.php");
                exit;
            }
        }
    }

    echo "Request with ID $requestId not found.";
    header("Refresh: 3; url=adminDashboard.php");
    exit;
} else {
    echo "Invalid request.";
    header("Refresh: 3; url=adminDashboard.php");
    exit;
}
?>