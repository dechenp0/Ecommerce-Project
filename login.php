<?php
session_start();
require_once 'config/db.php';
 
$error = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
 
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
 
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];
 
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: user/dashboard.php");
                }
                exit();
            } else {
                $error = 'Incorrect password. Please try again.';
            }
        } else {
            $error = 'No account found with that email.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login – Paperly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --blue-50:  #EBF5FF;
      --blue-100: #C7E4FF;
      --blue-200: #93CBFF;
      --blue-400: #3B9EF5;
      --blue-600: #1A6FBA;
      --blue-800: #0D3E6E;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: #F7FBFF;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .serif { font-family: 'DM Serif Display', serif; }
 
    /* Left decorative panel */
    .deco-panel {
      background: var(--blue-800);
      border-radius: 24px 0 0 24px;
      padding: 48px 40px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 560px;
      position: relative;
      overflow: hidden;
    }
    .deco-panel::before {
      content: '';
      position: absolute;
      width: 300px; height: 300px;
      border-radius: 50%;
      background: rgba(59,158,245,0.15);
      top: -80px; right: -80px;
    }
    .deco-panel::after {
      content: '';
      position: absolute;
      width: 200px; height: 200px;
      border-radius: 50%;
      background: rgba(59,158,245,0.1);
      bottom: -60px; left: -40px;
    }
 
    /* Form panel */
    .form-panel {
      background: #fff;
      border-radius: 0 24px 24px 0;
      padding: 48px 40px;
      min-height: 560px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
 
    /* Input styles */
    .input-wrap { position: relative; margin-bottom: 18px; }
    .input-wrap label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: #475569;
      margin-bottom: 6px;
    }
    .input-wrap input {
      width: 100%;
      padding: 11px 14px 11px 40px;
      border: 1.5px solid #d9eeff;
      border-radius: 10px;
      font-size: 14px;
      font-family: 'DM Sans', sans-serif;
      color: #1a2235;
      background: #F7FBFF;
      transition: border-color 0.2s, box-shadow 0.2s;
      outline: none;
    }
    .input-wrap input:focus {
      border-color: var(--blue-400);
      box-shadow: 0 0 0 3px rgba(59,158,245,0.12);
      background: #fff;
    }
    .input-icon {
      position: absolute;
      left: 12px;
      bottom: 11px;
      color: #93CBFF;
      pointer-events: none;
    }
    .toggle-pass {
      position: absolute;
      right: 12px;
      bottom: 11px;
      cursor: pointer;
      color: #93CBFF;
      background: none;
      border: none;
      padding: 0;
      line-height: 1;
    }
    .toggle-pass:hover { color: var(--blue-400); }
 
    /* Button */
    .btn-submit {
      width: 100%;
      padding: 13px;
      background: var(--blue-400);
      color: #fff;
      font-family: 'DM Sans', sans-serif;
      font-size: 15px;
      font-weight: 600;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: background 0.2s, transform 0.15s;
      margin-top: 6px;
    }
    .btn-submit:hover { background: var(--blue-600); transform: translateY(-1px); }
    .btn-submit:active { transform: translateY(0); }
 
    /* Error box */
    .error-box {
      background: #fff0f0;
      border: 1px solid #fca5a5;
      color: #dc2626;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 13px;
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
 
    /* Divider */
    .divider {
      display: flex; align-items: center; gap: 10px;
      color: #94a3b8; font-size: 12px; margin: 18px 0;
    }
    .divider::before, .divider::after {
      content: ''; flex: 1;
      height: 1px; background: #e2f0ff;
    }
 
    @keyframes fadeUp {
      from { opacity:0; transform: translateY(20px); }
      to   { opacity:1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp 0.45s ease both; }
  </style>
</head>
<body>
 
<div class="w-full max-w-3xl mx-4 shadow-xl rounded-3xl flex fade-up" style="min-height:560px;">
 
  <!-- ── LEFT DECORATIVE PANEL ── -->
  <div class="deco-panel w-2/5 hidden md:flex" style="flex-direction:column;justify-content:space-between;">
    <!-- Logo -->
    <a href="index.html" class="flex items-center gap-2 relative z-10">
      <div style="width:32px;height:32px;background:var(--blue-400);border-radius:9px;display:flex;align-items:center;justify-content:center;">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 4h10v2H4V8zm0 4h16v2H4v-2zm0 4h10v2H4v-2z" fill="#fff"/></svg>
      </div>
      <span class="serif text-2xl text-white">DechenShop</span>
    </a>
 
    <!-- Illustration -->
    <div class="relative z-10 flex justify-center py-6">
      <svg width="160" height="160" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="text-white opacity-90">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <path d="M16 10a4 4 0 0 1-8 0"></path>
      </svg>
    </div>
 
    <!-- Tagline -->
    <div class="relative z-10">
      <p class="serif text-white text-xl leading-snug mb-2">"Everything you need<br/>to write your story."</p>
      <p class="text-blue-300 text-xs">Quality stationery, delivered to your door.</p>
    </div>
  </div>
 
  <!-- ── RIGHT FORM PANEL ── -->
  <div class="form-panel flex-1">
    <!-- Mobile logo -->
    <a href="index.html" class="flex items-center gap-2 mb-6 md:hidden">
      <div style="width:28px;height:28px;background:var(--blue-400);border-radius:8px;display:flex;align-items:center;justify-content:center;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 4h10v2H4V8zm0 4h16v2H4v-2zm0 4h10v2H4v-2z" fill="#fff"/></svg>
      </div>
      <span class="serif text-xl" style="color:var(--blue-800);">DShop</span>
    </a>
 
    <h2 class="serif text-3xl mb-1" style="color:var(--blue-800);">Welcome back</h2>
    <p class="text-sm text-gray-400 mb-6">Sign in to your account</p>
 
    <!-- Error message -->
    <?php if ($error): ?>
    <div class="error-box">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
 
    <form method="POST" action="login.php" novalidate>
 
      <!-- Email -->
      <div class="input-wrap">
        <label for="email">Email address</label>
        <svg class="input-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <input type="email" id="email" name="email"
               placeholder="you@example.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               required autocomplete="email"/>
      </div>
 
      <!-- Password -->
      <div class="input-wrap">
        <label for="password">Password</label>
        <svg class="input-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <input type="password" id="password" name="password"
               placeholder="Enter your password"
               required autocomplete="current-password"/>
        <button type="button" class="toggle-pass" onclick="togglePass('password', this)" title="Show/hide password">
          <svg id="eye-password" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
 
      <!-- Remember + Forgot -->
      <div class="flex items-center justify-between mb-4 text-sm">
        <label class="flex items-center gap-2 cursor-pointer text-gray-500">
          <input type="checkbox" name="remember" style="accent-color:var(--blue-400);width:15px;height:15px;"/> Remember me
        </label>
        <a href="forgot_password.php" style="color:var(--blue-400);" class="hover:underline">Forgot password?</a>
      </div>
 
      <button type="submit" class="btn-submit">Sign In</button>
    </form>
 
    <div class="divider">or</div>
 
    <p class="text-center text-sm text-gray-500">
      Don't have an account?
      <a href="register.php" style="color:var(--blue-400);font-weight:600;" class="hover:underline">Create one free</a>
    </p>
  </div>
</div>
 
<script>
  function togglePass(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.innerHTML = isText
      ? `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`
      : `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
  }
</script>
</body>
</html>