<?php
$password_hash = password_hash('password', PASSWORD_BCRYPT);
echo $password_hash;
?>