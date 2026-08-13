<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ — Suchai Auto</title>
    <link rel="stylesheet" href="{{ asset('asset/bootstrap-5.3.8-dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/fontawesome-free-7.3.0-web/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/sweetalert2/dist/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/app.css') }}">
    <style>
        .login-card { animation: loginFadeIn .5s ease; }
        @keyframes loginFadeIn {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .login-logo-ring {
            width:72px; height:72px;
            background: linear-gradient(135deg, #F97316, #EA6A00);
            border-radius: 50%;
            display:flex; align-items:center; justify-content:center;
            font-size:32px; color:#fff;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(249,115,22,.35);
        }
    </style>
</head>
<body class="login-page">

<div class="login-card">
    <div class="text-center mb-4">
        <div class="login-logo-ring"><i class="fa-solid fa-car-wrench"></i></div>
        <h1 style="font-size:22px; font-weight:800; color:#1E293B; margin-bottom:4px;">Suchai Auto</h1>
        <p style="color:#64748B; font-size:14px;">ระบบบริหารงานอู่ซ่อมรถ</p>
    </div>

    @if($errors->has('login'))
    <div class="alert" style="background:#FEE2E2; color:#991B1B; border:none; border-radius:8px; padding:10px 14px; font-size:13px; margin-bottom:16px;">
        <i class="fa-solid fa-circle-xmark me-1"></i> {{ $errors->first('login') }}
    </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">ชื่อผู้ใช้</label>
            <div class="input-group">
                <span class="input-group-text" style="border-color:#E2E8F0; background:#F8FAFC;">
                    <i class="fa-solid fa-user" style="color:#94A3B8;"></i>
                </span>
                <input type="text" name="username" id="username"
                       class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username') }}"
                       placeholder="กรอกชื่อผู้ใช้" autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">รหัสผ่าน</label>
            <div class="input-group">
                <span class="input-group-text" style="border-color:#E2E8F0; background:#F8FAFC;">
                    <i class="fa-solid fa-lock" style="color:#94A3B8;"></i>
                </span>
                <input type="password" name="password" id="password"
                       class="form-control"
                       placeholder="กรอกรหัสผ่าน">
                <button type="button" class="btn btn-outline-secondary"
                        style="border-color:#E2E8F0;"
                        onclick="togglePw()" id="togglePwBtn">
                    <i class="fa-solid fa-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-primary-custom w-100 justify-content-center" style="padding:12px;">
            <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ
        </button>
    </form>

    <div class="text-center mt-4" style="color:#94A3B8; font-size:12px;">
        <i class="fa-solid fa-shield-halved me-1"></i>ระบบรักษาความปลอดภัย
    </div>
</div>

<script src="{{ asset('asset/jquery-3.7.1.min.js') }}"></script>
<script>
function togglePw() {
    const pw  = document.getElementById('password');
    const ico = document.getElementById('eyeIcon');
    if (pw.type === 'password') {
        pw.type = 'text';
        ico.className = 'fa-solid fa-eye-slash';
    } else {
        pw.type = 'password';
        ico.className = 'fa-solid fa-eye';
    }
}
</script>
</body>
</html>
