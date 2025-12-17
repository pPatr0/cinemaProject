<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pdo = require_once __DIR__ . '/../config/dbcon.php';

// Limitation
function rateLimitCheck(string $ip): array {
    $file = __DIR__ . '/rate_limit.json';
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

    $now = time();
    if (!isset($data[$ip])) {
        $data[$ip] = ['count' => 0, 'last' => $now];
    }

    // Reset
    if ($now - $data[$ip]['last'] > 900) {
        $data[$ip] = ['count' => 0, 'last' => $now];
    }

    $count = $data[$ip]['count'];
    $block = $count >= 5;                    
    return ['block' => $block, 'count' => $count, 'data' => &$data];
}

function rateLimitSave(string $ip, array &$info): void {
    $file = __DIR__ . '/rate_limit.json';
    $info['data'][$ip]['last'] = time();
    file_put_contents($file, json_encode($info['data'], JSON_PRETTY_PRINT));
}

function rateLimitClear(string $ip): void {
    $file = __DIR__ . '/rate_limit.json';
    if (!file_exists($file)) return;
    $data = json_decode(file_get_contents($file), true);
    unset($data[$ip]);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}


$clientIP = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$limit = rateLimitCheck($clientIP);

$error = '';

// Login
if (isset($_POST['login'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('CSRF token mismatch');
    }

    if ($limit['block']) {
        $error = 'Too many failed attempts. Please wait 15 minutes.';
    } else {
        $email = $_POST['email'] ?? '';
        $pass  = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password_hash'])) {
            rateLimitClear($clientIP);          
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_admin']  = $user['is_admin'];
            header("Location: profile.php");
            exit;
        } else {
            $limit['data'][$clientIP]['count']++;
            rateLimitSave($clientIP, $limit);
            $error = "Invalid email or password.";
        }
    }
}

// Signup
if (isset($_POST['signup'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('CSRF token mismatch');
    }

    $name  = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
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
        <div class="bg-red-900 border border-red-500 text-red-300 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
        <div class="bg-green-900 border border-green-500 text-green-300 px-4 py-3 rounded mb-4">
            Account created! You can now log in.
        </div>
    <?php endif; ?>

    <!-- Login -->
    <form method="POST" class="space-y-4">
        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="email" name="email" placeholder="Email" required
               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        <input type="password" name="password" placeholder="Password" required
               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        <button name="login" class="w-full bg-yellow-500 text-black py-2 rounded font-bold hover:bg-yellow-600">
            Login
        </button>
    </form>

    <div class="text-center my-4 text-gray-400">or</div>

    <!-- Signup -->
    <form method="POST" class="space-y-4">
        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="text" name="name" placeholder="Full Name" required
               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        <input type="email" name="email" placeholder="Email" required
               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        <input type="password" name="password" placeholder="Password" required
               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        <button name="signup" class="w-full bg-gray-700 text-white py-2 rounded font-bold hover:bg-gray-600">
            Sign Up
        </button>
    </form>
</div>
</body>
</html>