<?php
$password = 'password123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "Le nouveau hachage pour 'password123' est : <pre>" . $hashed_password . "</pre>";
?>