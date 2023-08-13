<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_destroy();
setcookie('flag', 'true', time() - 10, '/');


header("location: index.html");
?>