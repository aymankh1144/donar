<?php
session_start();
if (!empty($_SESSION['admin_logged_in'])) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>لوحة التحكم - MR. DONAR</title>
<link rel="icon" href="../assets/images/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#0d0d0d;font-family:'Cairo',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
.card{background:linear-gradient(145deg,#1e1b16,#141210);border:1px solid rgba(201,168,76,.2);border-radius:20px;padding:2.5rem 2rem;width:100%;max-width:400px;box-shadow:0 24px 60px rgba(0,0,0,.5)}
.logo{text-align:center;margin-bottom:2rem}
.logo img{width:80px;height:80px;border-radius:50%;border:2px solid rgba(201,168,76,.5)}
.logo h2{font-family:'Cairo',sans-serif;font-size:1.6rem;color:#E8C97A;margin-top:.75rem;letter-spacing:1px}
.logo p{font-size:13px;color:rgba(200,170,100,.55);margin-top:4px}
.group{margin-bottom:1.1rem}
.group label{display:block;margin-bottom:6px;font-size:13px;color:rgba(200,170,100,.6);font-weight:500}
.group input{width:100%;background:rgba(201,168,76,.07);border:1px solid rgba(201,168,76,.18);border-radius:10px;padding:11px 14px;color:#E8C97A;font-size:14px;font-family:'Cairo',sans-serif;outline:none;transition:border-color .25s}
.group input:focus{border-color:rgba(201,168,76,.45)}
.group input::placeholder{color:rgba(201,168,76,.25)}
.err{display:none;background:rgba(200,80,80,.12);border:1px solid rgba(200,80,80,.25);border-radius:8px;padding:9px 14px;color:#e57373;font-size:13px;text-align:center;margin-bottom:1rem}
.btn{width:100%;padding:12px;background:linear-gradient(135deg,#C9A84C,#8B6914);border:none;border-radius:10px;color:#111;font-size:15px;font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif;transition:opacity .25s,transform .1s}
.btn:hover{opacity:.88}
.btn:active{transform:scale(.98)}
.btn:disabled{opacity:.5;cursor:not-allowed}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <img src="../assets/images/logo.jpg" alt="Logo">
    <h2>MR. DONAR</h2>
    <p>لوحة إدارة المطعم</p>
  </div>
  <div class="err" id="err"></div>
  <div class="group"><label>اسم المستخدم</label><input type="text" id="u" placeholder="اسم المستخدم" autocomplete="username"></div>
  <div class="group"><label>كلمة المرور</label><input type="password" id="p" placeholder="••••••••" autocomplete="current-password" onkeydown="if(event.key==='Enter')login()"></div>
  <button class="btn" id="btn" onclick="login()">دخول</button>
</div>
<script>
const theme = localStorage.getItem('theme');
if (theme === 'dark') document.documentElement.dataset.theme = 'dark';
async function login() {
  const btn=document.getElementById('btn'), err=document.getElementById('err');
  const u=document.getElementById('u').value.trim(), p=document.getElementById('p').value;
  if (!u||!p){showErr('يرجى إدخال البيانات');return}
  btn.disabled=true; btn.textContent='جاري التحقق...';
  const fd=new FormData(); fd.append('action','login'); fd.append('username',u); fd.append('password',p);
  const res=await fetch('../api/admin.php',{method:'POST',body:fd}).then(r=>r.json()).catch(()=>({success:false}));
  if (res.success) { window.location.href='dashboard.php'; }
  else { showErr(res.message||'بيانات الدخول غير صحيحة'); btn.disabled=false; btn.textContent='دخول'; }
}
function showErr(m){const e=document.getElementById('err');e.textContent=m;e.style.display='block';setTimeout(()=>e.style.display='none',4000)}
</script>
</body>
</html>
