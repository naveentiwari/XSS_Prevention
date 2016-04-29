<?php
$file = file_get_contents($_SERVER['SCRIPT_FILENAME']);
$file = preg_replace('/^<\?php/', '', $file);
$file = str_replace("include('security3.php');", '', $file);
$file = preg_replace('/echo ([^;]+);/', 'safe_echo($1);', $file);
$file = '<?php

$__replacements = array();

function safe_echo($c)
{
    global $__replacements;
    $i = count($__replacements);
    $__replacements[] = $c;
    echo "\x00$i\x01";
}

function encode_self($string_val) {
    $encoder = new HTMLEncode(false);
    return $encoder->purify($string_val);
}

function do_replacements($buffer)
{
    global $__replacements;
    $headers = headers_list();
    $plain = false;
    foreach ($headers as $h) {
        if (preg_match("/^content-type: text\/plain/i", $h)) {
            $plain = true;
            break;
        }
    }

    foreach ($__replacements as $i => $value) {
        if ($plain && false) {
            $buffer = str_replace("\x00$i\x01", $value, $buffer);
        } else {
            $buffer = str_replace("\x00$i\x01", encode_self($value), $buffer);
        }
    }

    return $buffer;
}


ob_start("do_replacements");
' . $file;

/*
header('Content-type: text/plain; charset=utf-8');
echo $file;
die;
*/

$fname = tempnam('/tmp', 'a');
file_put_contents($fname, $file);
require $fname;
unlink($fname);

die;
