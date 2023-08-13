<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["adminAuthenticated"]) || $_SESSION["adminAuthenticated"] !== true) {
    header("location: admin_login.html");
    exit();
}

require_once('dbConnection.php');

$currentData = file_get_contents('pendingRequest.json');
$pendingRequests = json_decode($currentData, true);

$approvedData = file_get_contents('approvedRequests.json');
$approvedRequests = json_decode($approvedData, true);

$adminID = $_SESSION["userID"]; 

function decryptValue($encryptedValue) {
    return openssl_decrypt($encryptedValue, "aes-256-cbc", 3, 0, "}^(*^s83&ADP{>kd");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="adminDashboardStyle.css">
</head>

<body>
    <h1>Welcome to Admin Dashboard</h1>

    <h2>Pending Requests</h2>
    <table>
        <tr>
            <th>Request ID</th>
            <th>Sender</th>
            <th>Receiver</th>
            <th>Amount</th>
            <th>LeftOver</th>
            <th>Timestamp</th>
            <th>Vote Count</th>
            <th>Action</th>
        </tr>
        <?php
    if (!$pendingRequests) {
    } else {
        foreach ($pendingRequests as $request) {
            echo "<tr>";
            echo "<td>" . $request["requestId"] . "</td>";
            echo "<td>" . $request["sender"] . "</td>";
            echo "<td>" . decryptValue($request["receiver"]) . "</td>"; // Decrypt receiver
            echo "<td>" . decryptValue($request["amount"]) . "</td>";   // Decrypt amount
            echo "<td>" . decryptValue($request["leftover"]) . "</td>";   // Decrypt amount
            echo "<td>" . $request["timestamp"] . "</td>";
            echo "<td>" . $request["voteCount"] . "</td>";
            echo "<td>";
            if (!isset($request["approvedBy"][$adminID]) || !$request["approvedBy"][$adminID]) {
                echo "<a href='approveRequest.php?reqId=" . $request["requestId"] . "'>Approve</a>";
            } else {
                echo "Approved";
            }
            echo "</td>";
            echo "</tr>";
        }
    }
    ?>
    </table>
    <h2>Approved Requests</h2>
    <table>
        <tr>
            <th>Request ID</th>
            <th>Sender</th>
            <th>Receiver</th>
            <th>Amount</th>
            <th>Timestamp</th>
            <th>Vote Count</th>
        </tr>
        <?php
        if(!$approvedRequests)
        {
        }
        else
        {
            foreach ($approvedRequests as $request) {
                echo "<tr>";
                echo "<td>" . $request["requestId"] . "</td>";
                echo "<td>" . $request["sender"] . "</td>";
                echo "<td>" . $request["receiver"] . "</td>";
                echo "<td>" . $request["amount"] . "</td>";
                echo "<td>" . $request["timestamp"] . "</td>";
                echo "<td>" . $request["voteCount"] . "</td>";
                echo "</tr>";
            }
        }
        ?>
    </table>

    <a href="logout.php"><button>Logout</button></a>
</body>

</html>