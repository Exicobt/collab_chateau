<?php
session_start();
require_once 'config/database.php';

$errors = [];
$success = '';

// Handle Register
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $phone_number = trim($_POST['phone_number']);

    // Validate input
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if (empty($full_name)) $errors[] = "Full name is required";
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email already registered";
        }
    }

    // Register user if no errors
    if (empty($errors)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, phone_number) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash, $full_name, $phone_number]);
            
            $success = "Registration successful! Please login.";
            
        } catch(PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}

// Handle Login
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    
    // Attempt login
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch(PDOException $e) {
            $errors[] = "Login failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .slide-container {
            transition: transform 0.6s cubic-bezier(0.4, 0.0, 0.2, 1);
        }
        
        .slide-left {
            transform: translateX(-50%);
        }
        
        .slide-right {
            transform: translateX(0%);
        }
        
        .form-fade {
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        
        .form-hidden {
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
        }
        
        .form-visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        
        .aurora-bg {
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #ffeaa7);
            background-size: 400% 400%;
            animation: aurora 15s ease infinite;
        }
        
        @keyframes aurora {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.3);
        }
        
        .alert {
            padding: 8px 12px;
            margin-bottom: 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        
        .alert-success {
            background-color: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-6xl h-[600px] relative overflow-hidden rounded-3xl shadow-2xl">
        <!-- Sliding Container -->
        <div class="slide-container slide-right w-[200%] h-full flex">
            <!-- Login Side -->
            <div class="w-1/2 h-full flex">
                <!-- Visual Panel (Left) -->
                <div class="w-1/2 aurora-bg relative overflow-hidden flex items-center justify-center">
                    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
                    <div class="relative z-10 text-center text-white p-8">
                        <div class="mb-8">
                            <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-black" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <h1 class="text-4xl font-bold mb-4">
                            Be a Part of<br>
                            Something <span class="text-yellow-400">Beautiful</span>
                        </h1>
                        <p class="text-lg opacity-90">Join our community and discover amazing experiences</p>
                    </div>
                </div>
                
                <!-- Login Form (Right) -->
                <div class="w-1/2 bg-black flex items-center justify-center p-8">
                    <div id="loginForm" class="form-fade form-visible w-full max-w-sm">
                        <h2 class="text-3xl font-bold text-white mb-2">Login</h2>
                        <p class="text-gray-400 mb-6">Enter your credentials to access your account</p>
                        
                        <!-- Login Form -->
                        <form method="POST" class="space-y-5">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-error">
                                    <?php foreach($errors as $error): ?>
                                        <p><?php echo htmlspecialchars($error); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success">
                                    <p><?php echo htmlspecialchars($success); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div>
                                <label for="login-email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                                <input 
                                    type="email" 
                                    id="login-email" 
                                    name="email"
                                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none input-focus focus:border-yellow-400"
                                    placeholder="Enter your email"
                                    required
                                />
                            </div>
                            
                            <div>
                                <label for="login-password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                                <input 
                                    type="password" 
                                    id="login-password" 
                                    name="password"
                                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none input-focus focus:border-yellow-400"
                                    placeholder="Enter your password"
                                    required
                                />
                            </div>
                            
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="remember" 
                                    name="remember"
                                    class="w-4 h-4 text-yellow-400 bg-gray-800 border-gray-600 rounded focus:ring-yellow-400"
                                />
                                <label for="remember" class="ml-2 text-sm text-gray-300">Remember me</label>
                            </div>
                            
                            <button 
                                type="submit" 
                                name="login"
                                class="w-full bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-3 px-4 rounded-lg transition duration-200"
                            >
                                Login
                            </button>
                        </form>
                        
                        <p class="text-center text-gray-400 mt-6">
                            Not a member? 
                            <button 
                                onclick="switchToRegister()" 
                                class="text-yellow-400 hover:text-yellow-300 font-medium transition duration-200"
                            >
                                Create an account
                            </button>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Register Side -->
            <div class="w-1/2 h-full flex">
                <!-- Register Form (Left) -->
                <div class="w-1/2 bg-black flex items-center justify-center p-6">
                    <div id="registerForm" class="form-fade form-hidden w-full max-w-xs">
                        <h2 class="text-2xl font-bold text-white mb-1">Register</h2>
                        <p class="text-gray-400 mb-5 text-sm">Create your account to get started</p>
                        
                        <!-- Register Form -->
                        <form class="space-y-3" method="POST" action="">
                            <div>
                                <label for="register-username" class="block text-xs font-medium text-gray-300 mb-1">Username</label>
                                <input 
                                    type="text" 
                                    id="register-username" 
                                    name="username"
                                    class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none input-focus focus:border-yellow-400"
                                    placeholder="Choose a username"
                                    maxlength="50"
                                    required
                                />
                            </div>
                            
                            <div>
                                <label for="register-email" class="block text-xs font-medium text-gray-300 mb-1">Email</label>
                                <input 
                                    type="email" 
                                    id="register-email" 
                                    name="email"
                                    class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none input-focus focus:border-yellow-400"
                                    placeholder="Enter your email"
                                    maxlength="100"
                                    required
                                />
                            </div>
                            
                            <div>
                                <label for="register-fullname" class="block text-xs font-medium text-gray-300 mb-1">Full Name</label>
                                <input 
                                    type="text" 
                                    id="register-fullname" 
                                    name="full_name"
                                    class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none input-focus focus:border-yellow-400"
                                    placeholder="Enter your full name"
                                    maxlength="100"
                                    required
                                />
                            </div>
                            
                            <div>
                                <label for="register-phone" class="block text-xs font-medium text-gray-300 mb-1">Phone Number</label>
                                <input 
                                    type="tel" 
                                    id="register-phone" 
                                    name="phone_number"
                                    class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none input-focus focus:border-yellow-400"
                                    placeholder="Enter your phone number"
                                    maxlength="20"
                                />
                            </div>
                            
                            <div>
                                <label for="register-password" class="block text-xs font-medium text-gray-300 mb-1">Password</label>
                                <input 
                                    type="password" 
                                    id="register-password" 
                                    name="password"
                                    class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none input-focus focus:border-yellow-400"
                                    placeholder="Create a password"
                                    required
                                />
                            </div>
                            
                            <button 
                                type="submit" 
                                name="register"
                                class="w-full bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-2.5 px-4 rounded-lg transition duration-200 text-sm mt-4"
                            >
                                Create Account
                            </button>
                        </form>
                        
                        <p class="text-center text-gray-400 mt-4 text-sm">
                            Already have an account? 
                            <button 
                                onclick="switchToLogin()" 
                                class="text-yellow-400 hover:text-yellow-300 font-medium transition duration-200"
                            >
                                Sign in
                            </button>
                        </p>
                    </div>
                </div>
                
                <!-- Visual Panel (Right) -->
                <div class="w-1/2 aurora-bg relative overflow-hidden flex items-center justify-center">
                    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
                    <div class="relative z-10 text-center text-white p-8">
                        <div class="mb-8">
                            <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-black" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                            </div>
                        </div>
                        <h1 class="text-4xl font-bold mb-4">
                            Welcome to Our<br>
                            <span class="text-yellow-400">Community</span>
                        </h1>
                        <p class="text-lg opacity-90">Start your journey with us today</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isLoginView = true;
        
        function switchToRegister() {
            if (isLoginView) {
                // Hide login form
                document.getElementById('loginForm').classList.remove('form-visible');
                document.getElementById('loginForm').classList.add('form-hidden');
                
                // Slide container
                setTimeout(() => {
                    document.querySelector('.slide-container').classList.remove('slide-right');
                    document.querySelector('.slide-container').classList.add('slide-left');
                }, 150);
                
                // Show register form
                setTimeout(() => {
                    document.getElementById('registerForm').classList.remove('form-hidden');
                    document.getElementById('registerForm').classList.add('form-visible');
                }, 400);
                
                isLoginView = false;
            }
        }
        
        function switchToLogin() {
            if (!isLoginView) {
                // Hide register form
                document.getElementById('registerForm').classList.remove('form-visible');
                document.getElementById('registerForm').classList.add('form-hidden');
                
                // Slide container
                setTimeout(() => {
                    document.querySelector('.slide-container').classList.remove('slide-left');
                    document.querySelector('.slide-container').classList.add('slide-right');
                }, 150);
                
                // Show login form
                setTimeout(() => {
                    document.getElementById('loginForm').classList.remove('form-hidden');
                    document.getElementById('loginForm').classList.add('form-visible');
                }, 400);
                
                isLoginView = true;
            }
        }
    </script>
</body>
</html>