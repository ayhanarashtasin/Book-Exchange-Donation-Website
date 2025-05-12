<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $zilla = $_POST['zilla'];
    $city = $_POST['city'];
    $phone = $_POST['phone'];

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already exists. Please use a different email.";
        header("Location: Register.php");
        exit();
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, zilla, city, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $password, $zilla, $city, $phone);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful!";
        $_SESSION['registered_email'] = $email; // Store the email for login convenience
        header("Location: registration_success.php");
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: Register.php");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: Register.php");
}
?>