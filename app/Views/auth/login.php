<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — ARZANA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #0a0e17;
            --card-dark: #131824;
            --text-light: #ffffff;
            --text-dim: #8a94a6;
            --btn-blue: #0066ff;
            --cart-grad: linear-gradient(90deg, #007bff, #8a2be2);
            --input-bg: #1a2030;
            --border-subtle: rgba(255, 255, 255, 0.07);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;

            /* Subtle radial glow di background */
            background-image:
                radial-gradient(ellipse 60% 50% at 50% 0%, rgba(0, 102, 255, 0.08) 0%, transparent 70%),
                radial-gradient(ellipse 40% 30% at 80% 80%, rgba(138, 43, 226, 0.06) 0%, transparent 60%);
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
        }

        /* --- Branding di atas card --- */
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            justify-content: center;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-info h1 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: var(--text-light);
        }

        .brand-info p {
            font-size: 12px;
            color: var(--text-dim);
            margin-top: 1px;
        }

        /* --- Card --- */
        .card {
            background-color: var(--card-dark);
            border-radius: 18px;
            border: 1px solid var(--border-subtle);
            padding: 32px 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        }

        .card-header-text {
            margin-bottom: 26px;
        }

        .card-header-text h2 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-light);
        }

        .card-header-text p {
            font-size: 13px;
            color: var(--text-dim);
            margin-top: 4px;
        }

        /* --- Alert --- */
        .alert {
            background-color: rgba(220, 38, 38, 0.12);
            border: 1px solid rgba(220, 38, 38, 0.3);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: #f87171;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert i {
            font-size: 14px;
            flex-shrink: 0;
        }

        /* --- Form Fields --- */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-dim);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: var(--text-dim);
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            background-color: var(--input-bg);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            padding: 11px 14px 11px 40px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: var(--text-light);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-wrap input::placeholder {
            color: #3d4860;
        }

        .input-wrap input:focus {
            border-color: rgba(0, 102, 255, 0.5);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
        }

        /* Toggle password visibility */
        .toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: var(--text-dim);
            cursor: pointer;
            pointer-events: all;
            transition: color 0.2s;
        }

        .toggle-pw:hover {
            color: var(--text-light);
        }

        /* --- Submit Button --- */
        .btn-submit {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: var(--cart-grad);
            color: white;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            letter-spacing: 0.3px;
            transition: opacity 0.2s, transform 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
            opacity: 1;
        }

        /* --- Footer kecil --- */
        .login-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 12px;
            color: var(--text-dim);
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- Branding -->
    <div class="brand">
        <div class="brand-logo">
            <img src="<?= base_url('assets/img/logo_k.png') ?>" alt="Logo Arzana">
        </div>
        <div class="brand-info">
            <h1>Arzana</h1>
            <p>Sistem Pemesanan</p>
        </div>
    </div>

    <!-- Card Login -->
    <div class="card">
        <div class="card-header-text">
            <h2>Masuk ke Akun</h2>
            <p>Silakan masukkan kredensial Anda</p>
        </div>

        <?php if(session()->getFlashdata('msg')): ?>
        <div class="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= session()->getFlashdata('msg') ?>
        </div>
        <?php endif; ?>

        <form action="<?= base_url('/auth/login') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-user"></i>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                    >
                    <i class="fa-solid fa-eye toggle-pw" id="togglePw"></i>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                Masuk
            </button>
        </form>
    </div>

    <p class="login-footer">&copy; <?= date('Y') ?> Arzana. All rights reserved.</p>
</div>

<script>
    const togglePw = document.getElementById('togglePw');
    const pwInput = document.getElementById('password');

    togglePw.addEventListener('click', () => {
        const isHidden = pwInput.type === 'password';
        pwInput.type = isHidden ? 'text' : 'password';
        togglePw.classList.toggle('fa-eye', !isHidden);
        togglePw.classList.toggle('fa-eye-slash', isHidden);
    });
</script>

</body>
</html>