<?php
session_start();
require_once 'config/db.php';
 
$error   = '';
$success = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
 
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
 
        if ($check->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $full_name, $email, $hashed);
 
            if ($stmt->execute()) {
                $success = 'Account created successfully! Redirecting to login...';
                header("Refresh: 2; url=login.php");
            } else {
                $error = 'Something went wrong. Please try again.';
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register – Paperly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --blue-50:#EBF5FF; --blue-100:#C7E4FF; --blue-200:#93CBFF;
      --blue-400:#3B9EF5; --blue-600:#1A6FBA; --blue-800:#0D3E6E;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family:'DM Sans',sans-serif; background:#F7FBFF; min-height:100vh; display:flex; align-items:center; justify-content:center; padding: 24px 16px; }
    .serif { font-family:'DM Serif Display',serif; }
    .deco-panel {
      background:var(--blue-800); border-radius:24px 0 0 24px;
      padding:48px 40px; display:flex; flex-direction:column;
      justify-content:space-between; position:relative; overflow:hidden;
    }
    .deco-panel::before {
      content:''; position:absolute; width:280px; height:280px;
      border-radius:50%; background:rgba(59,158,245,0.15); top:-80px; right:-60px;
    }
    .deco-panel::after {
      content:''; position:absolute; width:180px; height:180px;
      border-radius:50%; background:rgba(59,158,245,0.08); bottom:-50px; left:-30px;
    }
    .form-panel { background:#fff; border-radius:0 24px 24px 0; padding:48px 40px; display:flex; flex-direction:column; justify-content:center; }
    .input-wrap { position:relative; margin-bottom:16px; }
    .input-wrap label { display:block; font-size:13px; font-weight:500; color:#475569; margin-bottom:5px; }
    .input-wrap input {
      width:100%; padding:11px 14px 11px 40px;
      border:1.5px solid #d9eeff; border-radius:10px;
      font-size:14px; font-family:'DM Sans',sans-serif; color:#1a2235;
      background:#F7FBFF; transition:border-color 0.2s,box-shadow 0.2s; outline:none;
    }
    .input-wrap input:focus { border-color:var(--blue-400); box-shadow:0 0 0 3px rgba(59,158,245,0.12); background:#fff; }
    .input-wrap input.error-input { border-color:#fca5a5; }
    .input-icon { position:absolute; left:12px; bottom:11px; color:#93CBFF; pointer-events:none; }
    .toggle-pass { position:absolute; right:12px; bottom:11px; cursor:pointer; color:#93CBFF; background:none; border:none; padding:0; }
    .toggle-pass:hover { color:var(--blue-400); }
    .btn-submit {
      width:100%; padding:13px; background:var(--blue-400); color:#fff;
      font-family:'DM Sans',sans-serif; font-size:15px; font-weight:600;
      border:none; border-radius:10px; cursor:pointer;
      transition:background 0.2s,transform 0.15s; margin-top:4px;
    }
    .btn-submit:hover { background:var(--blue-600); transform:translateY(-1px); }
    .error-box { background:#fff0f0; border:1px solid #fca5a5; color:#dc2626; border-radius:8px; padding:10px 14px; font-size:13px; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
    .success-box { background:#f0fdf4; border:1px solid #86efac; color:#16a34a; border-radius:8px; padding:10px 14px; font-size:13px; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
    .strength-bar { height:4px; border-radius:4px; background:#e2f0ff; margin-top:6px; overflow:hidden; }
    .strength-fill { height:100%; border-radius:4px; width:0%; transition:width 0.3s, background 0.3s; }
    .strength-label { font-size:11px; color:#94a3b8; margin-top:3px; }
    .divider { display:flex; align-items:center; gap:10px; color:#94a3b8; font-size:12px; margin:16px 0; }
    .divider::before, .divider::after { content:''; flex:1; height:1px; background:#e2f0ff; }
    @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
    .fade-up { animation:fadeUp 0.45s ease both; }
  </style>
</head>
<body>
 
<div class="w-full max-w-3xl shadow-xl rounded-3xl flex fade-up" style="min-height:600px;">
 
  <!-- LEFT PANEL -->
  <div class="deco-panel w-2/5 hidden md:flex flex-col justify-between">
    <a href="index.html" class="flex items-center gap-2 relative z-10">
      <div style="width:32px;height:32px;background:var(--blue-400);border-radius:9px;display:flex;align-items:center;justify-content:center;">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 4h10v2H4V8zm0 4h16v2H4v-2zm0 4h10v2H4v-2z" fill="#fff"/></svg>
      </div>
      <span class="serif text-2xl text-white">DechenShop</span>
    </a>
 
    <div class="relative z-10 flex justify-center py-6">
      <svg width="160" height="160" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="text-white opacity-90">
        <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
        <line x1="12" y1="22.08" x2="12" y2="12"></line>
      </svg>
    </div>
 
    <div class="relative z-10">
      <p class="serif text-white text-xl leading-snug mb-2">"Join thousands of<br/>stationery lovers."</p>
      <div class="flex flex-col gap-2 mt-4">
        <div class="flex items-center gap-2 text-blue-200 text-xs"><span style="color:#3B9EF5;">✓</span> Free account, always</div>
        <div class="flex items-center gap-2 text-blue-200 text-xs"><span style="color:#3B9EF5;">✓</span> 10% off your first order</div>
        <div class="flex items-center gap-2 text-blue-200 text-xs"><span style="color:#3B9EF5;">✓</span> Track orders easily</div>
      </div>
    </div>
  </div>
 
  <!-- RIGHT FORM PANEL -->
  <div class="form-panel flex-1">
    <a href="index.html" class="flex items-center gap-2 mb-5 md:hidden">
      <div style="width:28px;height:28px;background:var(--blue-400);border-radius:8px;display:flex;align-items:center;justify-content:center;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 4h10v2H4V8zm0 4h16v2H4v-2zm0 4h10v2H4v-2z" fill="#fff"/></svg>
      </div>
      <span class="serif text-xl" style="color:var(--blue-800);">Paperly</span>
    </a>
 
    <h2 class="serif text-3xl mb-1" style="color:var(--blue-800);">Create account</h2>
    <p class="text-sm text-gray-400 mb-5">Join Paperly and start shopping</p>
 
    <?php if ($error): ?>
    <div class="error-box">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
 
    <?php if ($success): ?>
    <div class="success-box">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
 
    <form method="POST" action="register.php" novalidate id="regForm">
 
      <!-- Full Name -->
      <div class="input-wrap">
        <label for="full_name">Full Name</label>
        <svg class="input-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <input type="text" id="full_name" name="full_name"
               placeholder="Your full name"
               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
               required autocomplete="name"/>
      </div>
 
      <!-- Email -->
      <div class="input-wrap">
        <label for="email">Email Address</label>
        <svg class="input-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <input type="email" id="email" name="email"
               placeholder="you@example.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               required autocomplete="email"/>
      </div>
 
      <!-- Password -->
      <div class="input-wrap">
        <label for="password">Password</label>
        <svg class="input-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <input type="password" id="password" name="password"
               placeholder="Min. 6 characters"
               required autocomplete="new-password"
               oninput="checkStrength(this.value)"/>
        <button type="button" class="toggle-pass" onclick="togglePass('password',this)">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
        <p class="strength-label" id="strengthLabel">Enter a password</p>
      </div>
 
      <!-- Confirm Password -->
      <div class="input-wrap">
        <label for="confirm_password">Confirm Password</label>
        <svg class="input-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <input type="password" id="confirm_password" name="confirm_password"
               placeholder="Repeat your password"
               required autocomplete="new-password"/>
        <button type="button" class="toggle-pass" onclick="togglePass('confirm_password',this)">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
 
      <!-- Terms -->
      <label class="flex items-start gap-2 text-sm text-gray-500 mb-4 cursor-pointer">
        <input type="checkbox" name="terms" required style="accent-color:var(--blue-400);margin-top:2px;width:15px;height:15px;flex-shrink:0;"/>
        I agree to the <a href="#" style="color:var(--blue-400);" class="hover:underline mx-1">Terms of Service</a> and <a href="#" style="color:var(--blue-400);" class="hover:underline ml-1">Privacy Policy</a>
      </label>
 
      <button type="submit" class="btn-submit">Create Account</button>
    </form>
 
    <div class="divider">or</div>
 
    <p class="text-center text-sm text-gray-500">
      Already have an account?
      <a href="login.php" style="color:var(--blue-400);font-weight:600;" class="hover:underline">Sign in</a>
    </p>
  </div>
</div>
 
<script>
function togglePass(id, btn) {
  const input = document.getElementById(id);
  const show = input.type === 'password';
  input.type = show ? 'text' : 'password';
  btn.innerHTML = show
    ? `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
    : `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
}
 
function checkStrength(val) {
  const fill  = document.getElementById('strengthFill');
  const label = document.getElementById('strengthLabel');
  let score = 0;
  if (val.length >= 6)  score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const levels = [
    { w:'0%',   bg:'#e2f0ff', txt:'Enter a password' },
    { w:'25%',  bg:'#ef4444', txt:'Weak' },
    { w:'50%',  bg:'#f97316', txt:'Fair' },
    { w:'75%',  bg:'#eab308', txt:'Good' },
    { w:'100%', bg:'#22c55e', txt:'Strong 💪' },
  ];
  const level = levels[Math.min(score, 4)];
  fill.style.width      = level.w;
  fill.style.background = level.bg;
  label.textContent     = level.txt;
  label.style.color     = level.bg;
}
</script>
</body>
</html>