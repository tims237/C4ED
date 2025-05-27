<?php
session_start();
//on vide la session
session_unset();
//on détruit la session 
session_destroy();
header("Location: ../public/login.php");
exit();
?>