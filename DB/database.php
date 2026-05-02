<?php 
$db_server="localhost";
$db_user= "root";
$db_pass="";
$db_name="EDM";
$port = 3306;
$conn ="";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
     

try{
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name, $port);  
}
catch(mysqli_sql_exception){
    echo "<script> alert('Wrong credentials for database connection')</script>";
}
?>
