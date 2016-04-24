<?php

/* Check the post request if it contains any string that has
 * possibility of cross-site scripting
 */
foreach ($_POST as $key => $value) {
    $_POST[$key] = htmlentities  ($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/* Check the get request if it contains any string that has
 * possibility of cross-site scripting
 */
foreach ($_GET as $key => $value) {
    $_GET[$key] = htmlentities  ($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>
