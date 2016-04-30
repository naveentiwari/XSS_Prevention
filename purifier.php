<?php
include ('sqlparser.php');
include ('encodewrapper.php');
include ('DbQueryWrapper.php');

$retval = runkit_function_copy('mysql_query', '__mysql_query');
$retval = runkit_function_copy('mysql_fetch_array', '__mysql_fetch_array');
$retval = runkit_function_copy('fread', '__fread');

function InitializeLib ($lib = 'HTMLEncode') {
    global $encode;
    global $qryflag;

    $encode = new EncodeWrapper($lib);
    $qryflag = false;
}

function InitializeSafeQry ($dbhost, $dbport, $dbname, $dbuser, $dbpasswd) {
    global $qryobj;
    global $qryflag;

    $qryobj = new DBQueryWrapper($dbhost, $dbport, $dbname, $dbuser, $dbpasswd);

    if ($qryobj->IsInitialized ()) {
        $qryflag = true;
    }
}

function SanitizeUserData ($userdata) {

    global $encode;
    if (gettype($userdata) == 'string') {
        return $encode->purify ($userdata);
    }

    if (gettype($userdata) == 'array') {
        foreach ($userdata as $key => $value) {
            $userdata[$key] = SanitizeUserData ($value);
        }

        return $userdata;
    }

    return $userdata;
}


function safe_mysql_query($sql,$conn) {
    global $qryobj;
    global $qryflag;

    $p = new sqlParser($sql);
    $parsed_qry = $p->parse();

    if ($qryflag == true) {
        return $qryobj->executeQuery ($sql);
    }

    // execute the query and return the result
    return __mysql_query($sql, $conn);
}

function safe_mysql_fetch_array($result, $type) {
    global $qryobj;
    global $qryflag;

    if ($qryflag == true) {
        return $qryobj->fetchArray ($sql);
    }

    $result = __mysql_fetch_array($result, $type);

    // sanitize the user data for all XSS related issues
    $result = SanitizeUserData ($result);

    return $result;
}

function safe_fread($handle, $length) {
    return SanitizeUserData(__fread($handle, $length));
}

if ($retval) {
    runkit_function_redefine ('mysql_query', '$sql,$conn', 'return safe_mysql_query($sql, $conn);');
    runkit_function_redefine ('mysql_fetch_array', '$result,$type', 'return safe_mysql_fetch_array ($result, $type);');
    runkit_function_redefine ('fread', '$handle,$length', 'return safe_fread($handle, $length);');
}

foreach ($_GET as $key => $value) {
    $_GET[$key] = htmlentities  ($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>
