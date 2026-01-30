<?php
// Generate new password hash
$new_password = "admin123"; // Change this to your desired password
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

echo "New password: " . $new_password . "<br>";
echo "Hashed password: " . $hashed . "<br><br>";
echo "Copy the hashed password above and use it in Method 2";
?>