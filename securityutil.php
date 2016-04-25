<?php
require('HTMLPurifier.standalone.php');

/* Configuration for html purifier library */
$config = HTMLPurifier_Config::createDefault();
$config->set('Core.Encoding', 'UTF-8');
$config->set('Cache.SerializerPath', '/tmp');
$purifier = new HTMLPurifier($config);


/*
 * dummy implementation of the sql injection validation function
 */
function ValidateParsedQueryForSQLInjection ($parsed_qry, $query) {
    return true;
}

function SanitizeUserDataArray($userdata) {
    foreach ($userdata as $key => $value) {
        $userdata[$key] = SanitizeUserData ($value);
    }

    return $userdata;
}

function SanitizeUserDataString ($userdata) {
    global $purifier;
    return $purifier->purify($userdata);
}

function SanitizeUserData ($userdata) {

    if (gettype($userdata) == 'string')
        return SanitizeUserDataString ($userdata);

    if (gettype($userdata) == 'array')
        return SanitizeUserDataArray($userdata);

    /* the following data-types do not sanitization
     * or atleast i don't know how to sanitize them
     * 
     * boolean, integer, double, object, resource, NULL,
     * unknown type
     *
     * Moreover - apart from string, not other data-type
     * is expected in the call hierarchy
     */

    return $userdata;
}

/* Check the get request if it contains any string that has
 * possibility of cross-site scripting
 * It is a get request where we do not expect to pass any html
 * tag.
 */
foreach ($_GET as $key => $value) {
    $_GET[$key] = htmlentities  ($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

?>
