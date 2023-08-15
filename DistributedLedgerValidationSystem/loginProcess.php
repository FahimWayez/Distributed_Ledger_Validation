<?php
    if (session_status() === PHP_SESSION_NONE) 
    {
        session_start(); 
    } 

    $_SESSION['userName'] = $_POST['userName'];
    $_SESSION['password'] = $_POST['password'];
    
    $jsonCode = file_get_contents('loginProcess.json');
    $loginCode = json_decode($jsonCode, true);
    $phpCode = $loginCode['code'];
    $handle = fopen('login.php', 'w'); 
    fwrite($handle, $phpCode);
    fclose($handle); 
    header("Location: login.php"); 

?>