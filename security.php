<?php
include('sqlparser.php');
include('securityutil.php');

$retval = runkit_function_copy('mysql_query', '__mysql_query');
$retval = runkit_function_copy('mysql_fetch_array', '__mysql_fetch_array');
$retval = runkit_function_copy('fread', '__fread');

function safe_mysql_query($sql,$conn) {
    $p = new sqlParser($sql);
    $parsed_qry = $p->parse();

    // $parsed_qry can be validated here for sql injection
    // this will stop all injected queries going to
    // the database.
    if (ValidateParsedQueryForSQLInjection ($parsed_qry, $sql) == false) {
        return array();
    }

    // execute the query and return the result
    return __mysql_query($sql, $conn);
}

function safe_mysql_fetch_array($result, $type) {
    $result = __mysql_fetch_array($result, $type);

    // sanitize the user data for all XSS related issues
    $result = SanitizeUserData ($result);

    return $result;
}

function safe_fread($handle, $length) {
    return SanitizeUserData(__fread($handle, $length));
}

if ($retval) {
    redefine_function ('mysql_query', '$sql,$conn', 'return safe_mysql_query($sql, $conn);');
    redefine_function ('mysql_fetch_array', '$result,$type', 'return safe_mysql_fetch_array ($result, $type);');
    redefine_function ('fread', '$handle,$length', 'return safe_fread($handle, $length);');
}

?>
