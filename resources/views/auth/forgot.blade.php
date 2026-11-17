<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Kata Sandi — E-LIBRA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root{
            --pink-50:#F9F7F3; --pink-100:#F9F7F3; --pink-150:#E8EDF0;
            --pink-300:#BBDCE0; --pink-400:#A0CDD4; --pink-500:#4DCCD5;
            --pink-600:#30C4CE; --pink-700:#279CA4; --pink-800:#2A4B84;
            --ink:#13223B; --ink-soft:#24939B; --ink-soft2:#3CC7D1;
        }
        *, *::before, *::after{ box-sizing: border-box; margin: 0; padding: 0; }
        body{
            font-family: 'Manrope', sans-serif;
            color: var(--ink);
            background: #F9F7F3;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            -webkit-font-smoothing: antialiased;
        }
        a{ text-decoration: none; color: inherit; }
        input, button{ font-family: inherit; }
        .ic{ stroke: currentColor; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

        .auth-card{
            width: 100%;
            max-width: 460px;
            background: #fff;
            border-radius: 22px;
            padding: 42px 40px;
            box-shadow: 0 30px 60px -30px rgba(40,71,126,.4);
            border: 1px solid var(--pink-150);
        }
        .brand{
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: 'Fraunces', serif;
            font-weight: 700;
            font-size: 20px;
            color: var(--pink-800);
            margin-bottom: 26px;
        }
        .brand .mark{
            width: 34px; height: 34px;
            border-radius: 10px;
            background: linear-gradient(150deg, var(--pink-400), var(--pink-700));
            display: flex; align-items: center; justify-content: center;
            color: #fff;
        }
        .icon-badge{
            width: 60px; height: 60px;
            border-radius: 16px;
            background: var(--pink-100);
            color: var(--pink-700);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 18px;
        }
        h2{
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 28px;
            margin: 0 0 8px;
            letter-spacing: -.5px;
        }
        p.sub{
            font-size: 13.5px;
            color: var(--ink-soft);
            margin-bottom: 24px;
            line-height: 1.65;
        }
        .alert{
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 12.5px;
            font-weight: 700;
            margin-bottom: 18px;
            line-height: 1.5;
        }
        .alert-error{ background: #EAEFF1; color: #2A4B84; border: 1px solid #C5E1E5; }
        .alert-success{ background: #E1F3E9; color: #2D7F56; border: 1px solid #C4E8D5; }

        .form-label{
            display: block;
            font-size: 12px;
            font-weight: 800;
            color: var(--ink-soft);
            margin-bottom: 8px;
        }
        .form-field{
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1.5px solid var(--pink-150);
            border-radius: 12px;
            padding: 14px 16px;
            background: var(--pink-50);
            transition: border-color .2s ease;
        }
        .form-field:focus-within{ border-color: var(--pink-400); background: #fff; }
        .form-field input{
            border: none; background: none; outline: none;
            flex: 1; font-size: 13.5px; font-weight: 600; color: var(--ink);
        }
        .form-field svg{ color: var(--ink-soft2); }

        .btn-primary-lg{
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            margin-top: 22px;
            padding: 15px;
            background: linear-gradient(150deg, var(--pink-500), var(--pink-700));
            color: #fff;
            font-size: 13.5px;
            font-weight: 800;
            border: none;
            border-radius: 13px;
            cursor: pointer;
            box-shadow: 0 18px 28px -12px rgba(39,156,164,.65);
            transition: transform .2s ease;
        }
        .btn-primary-lg:hover{ transform: translateY(-2px); }

        .back-link{
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 22px;
            font-size: 12.5px;
            font-weight: 700;
            color: var(--pink-700);
        }
        .back-link:hover{ text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-card">
        <a href="/" class="brand">
            <div class="mark">
                <svg class="ic" width="16" height="16" viewBox="0 0 24 24" style="color:#fff">
                    <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
                </svg>
            </div>
            E-LIBRA
        </a>

        <div class="icon-badge">
            <svg class="ic" width="26" height="26" viewBox="0 0 24 24">
                <rect x="3" y="11" width="18" height="10" rx="2"/>
                <path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
        </div>

        <h2>Lupa Kata Sandi?</h2>
        <p class="sub">Masukkan email atau NIS akunmu. Kami akan mengirim link untuk mengatur ulang kata sandi ke email yang terdaftar.</p>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label class="form-label">Email atau NIS</label>
            <div class="form-field">
                <input type="text" name="identifier" value="{{ old('identifier') }}" required autofocus placeholder="Email atau NIS">
                <svg class="ic" width="16" height="16" viewBox="0 0 24 24">
                    <rect x="2" y="4" width="20" height="16" rx="3"/><path d="M2 7l10 6 10-6"/>
                </svg>
            </div>

            <button type="submit" class="btn-primary-lg">
                Kirim Link Reset
                <svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </form>

        <a href="{{ route('login') }}" class="back-link">
            <svg class="ic" width="13" height="13" viewBox="0 0 24 24"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            Kembali ke halaman Login
        </a>
    </div>
</body>
</html>