<?php
session_start();
$pdo = require_once 'dbcon.php';

$error = '';

// login
if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $pass_input = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass_input, $user['password_hash'])) {
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['is_admin']  = $user['is_admin'];

    header("Location: profile.php");
    exit;
    } else {
        $error = "Invalid email or password.";
    }
}

// signup
if (isset($_POST['signup'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass_input = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $hash = password_hash($pass_input, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO User (name, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hash]);
            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            $error = "Email already registered.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login / Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
<div class="container mx-auto px-4 py-12 max-w-md">
    <h1 class="text-3xl font-bold mb-6 text-center">Welcome to CineMax</h1>

    <?php if ($error): ?>
        <div class="bg-red-900 border border-red-500 text-red-300 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
        <div class="bg-green-900 border border-green-500 text-green-300 px-4 py-3 rounded mb-4">Account created! You can now log in.</div>
    <?php endif; ?>

    <!-- login form -->
    <form method="POST" class="space-y-4">
        <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        <button name="login" class="w-full bg-yellow-500 text-black py-2 rounded font-bold hover:bg-yellow-600">Login</button>
    </form>

    <div class="text-center my-4 text-gray-400">or</div>

    <!-- signup form -->
    <form method="POST" class="space-y-4">
        <input type="text" name="name" placeholder="Full Name" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        <button name="signup" class="w-full bg-gray-700 text-white py-2 rounded font-bold hover:bg-gray-600">Sign Up</button>
    </form>
</div>
</body>
</html>