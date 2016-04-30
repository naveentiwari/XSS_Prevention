<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once ('purifier.php');
require_once ('htmlencode.php');
require_once ('safe_echo.php');

InitializeLib ();
InitializeSafeQry ('localhost', '8889', 'blogdb', 'root', 'navii');
?>

<!DOCTYPE html>
<html>
<head><title>Naveen</title></head>
<body>
<p>
<script>alert("from developer")</script>
<?php
echo '<script> alert("coded in file") </script>';
?>
<img src=/ <?php echo 'onerror="malicious"'; ?> >
</p>
</body>
</html>
