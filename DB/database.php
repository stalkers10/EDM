<?php 
$db_server="sql208.infinityfree.com";
$db_user= "if0_41812042";
$db_pass="Ngueguim1";
$db_name="if0_41812042_edm";
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
