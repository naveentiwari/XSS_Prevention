<?php
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
    return 'Naveen';
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

?>
