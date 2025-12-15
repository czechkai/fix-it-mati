$input_code = $_POST['verification_code'];
$email = $_POST['email'];

$sql = "SELECT * FROM users WHERE email = :email AND verification_code = :code";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':email' => $email,
    ':code' => $input_code
]);
$user = $stmt->fetch();

if ($user) {
    // success → mark verified
    $update = "UPDATE users SET is_verified = TRUE, verification_code = NULL WHERE email = :email";
    $stmt = $conn->prepare($update);
    $stmt->execute([':email' => $email]);
    echo "Verified!";
} else {
    echo "Error verifying code.";
}