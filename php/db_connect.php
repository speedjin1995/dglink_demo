<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
//$db = mysqli_connect("localhost", "u865368203_demo", "@Sync5500", "u865368203_demo");
$db = mysqli_connect("srv1413.hstgr.io", "u865368203_demo", "@Sync5500", "u865368203_demo");

if(mysqli_connect_errno()){
    echo 'Database connection failed with following errors: ' . mysqli_connect_error();
    die();
}
?>