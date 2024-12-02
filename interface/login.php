<?php
require_once '../controller/UserController.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController = new UserController();
    if ($userController->login($_POST['username'], $_POST['password'])) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-sky-200 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-2xl rounded-xl overflow-hidden transform transition-all duration-300 hover:scale-[1.02]">
            <div class="p-8">
                <div class="flex justify-center mb-6">
                    <i class="ri-book-2-line text-5xl text-sky-600"></i>
                </div>
                <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">
                    Library Management
                </h2>

                <?php if (isset($error_message)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 flex items-center">
                        <i class="ri-error-warning-line mr-3 text-xl"></i>
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                <i class="ri-user-line text-gray-400"></i>
                            </span>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 transition duration-300"
                                placeholder="Enter your username"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                <i class="ri-lock-line text-gray-400"></i>
                            </span>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 transition duration-300"
                                placeholder="Enter your password"
                            >
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-sky-600 text-white py-2 rounded-lg hover:bg-sky-700 transition duration-300 ease-in-out transform hover:scale-[1.02] flex items-center justify-center"
                    >
                        <i class="ri-login-box-line mr-2"></i>
                        Login
                    </button>
                </form>
            </div>
        </div>
        <div class="text-center mt-4 text-sm text-gray-600">
            © <?php echo date('Y'); ?> Library Management System
        </div>
    </div>
</body>
</html>