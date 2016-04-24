<?php

$retval = runkit_function_copy('mysql_query', 'orig_mysql_query');

function safe_mysql_query($sql,$conn) {
    echo $sql;
}

$retval = runkit_function_redefine ('mysql_query', '$sql,$conn', 'return safe_mysql_query($sql, $conn);');

$username = 'username';
$fname    = 'fname';
$lname    = 'lname';
$email    = 'email';
$hashedpassword = 'hashedpasswd';
$sql = "INSERT INTO user_info  (username,shpasswd,fname,lname,email) VALUES ".
       "('$username','$hashedpassword', '$fname', '$lname','$email')";

mysql_query($sql, 'b');
?>
