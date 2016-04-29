<?php
require_once('htmlencode.php');
include('security3.php');
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
