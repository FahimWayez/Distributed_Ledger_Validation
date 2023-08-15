<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["clientAuthenticated"]) || $_SESSION["clientAuthenticated"] !== true) {
    header("location: index.html");
    exit(); 
}
require_once('dbConnection.php');

$handle = fopen('login.php', 'w');
fwrite($handle, '');
fclose($handle);

$clientID = $_SESSION["userID"];

$conn = dbConnection();
$selectTakaQuery = "SELECT taka FROM loginCredentials WHERE userID = '$clientID'";
$result = $conn->query($selectTakaQuery);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $taka = $row["taka"];
} else {
    $taka = "";
}
$conn->close();

$requests = [];
$pendingRequestFile = "pendingRequest.json";

if (file_exists($pendingRequestFile)) {
    $jsonData = file_get_contents($pendingRequestFile);
    if ($jsonData !== false) {
        $requests = json_decode($jsonData, true);
    }
}

$currentTimestamp = time();

if (!empty($requests)) {
    foreach ($requests as $key => $request) {
        $requestTimestamp = $request["timestamp"];
        if (($currentTimestamp - $requestTimestamp) >= 3600) {
            unset($requests[$key]);
        }
    }
    $jsonData = json_encode(array_values($requests), JSON_PRETTY_PRINT);
    file_put_contents($pendingRequestFile, $jsonData);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Money Transfer Form</title>
    <link rel="stylesheet" href="clientDashboardStyle.css">
</head>

<body>
    <br>
    <h2>Welcome to Your Dashboard</h2>
    <h2>Your available balance: <?php echo $taka; ?></h2>
    <form method="post" action="processForm.php">
        <h2>Request Money Transfer</h2>
        <input type="text" class="textBox" id="receiver" name="receiver" placeholder="Receiver">
        <input type="text" class="textBox" id="amount" name="amount" placeholder="Amount">
        <input type="password" class="textBox" id="pin" name="pin" placeholder="PIN">
        <input type="hidden" name="current_time" value="<?php echo time(); ?>">
        <input type="hidden" name="taka" value="<?php echo $taka; ?>">
        <input type="submit" value="Send Money">
    </form>
    <a href="logout.php"><button>Logout</button></a>
</body>

</html>