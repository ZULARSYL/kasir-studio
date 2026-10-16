<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Masuk | Portal PMB WIGA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
    :root {
        --brand-primary: #007B8B;
        --brand-primary-dark: #002025;
        --brand-primary-light: #FFF4EB;
        --brand-dark: #0F172A;
        --brand-slate: #475569;
        --brand-muted: #64748B;
        --border-color: rgba(15, 23, 42, 0.1);
    }

    .login-page-wrapper {
        background: radial-gradient(circle at 10% 20%, rgba(244, 123, 32, 0.06) 0%, transparent 45%),
                    radial-gradient(circle at 90% 80%, rgba(244, 123, 32, 0.04) 0%, transparent 45%),
                    #F8FAFC;
        min-height: 85vh;
    }

    /* Main Container Card */
    .auth-card {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 28px;
        box-shadow: 0 20px 50px -15px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    /* Left Sidebar */
    .auth-sidebar {
        background: linear-gradient(145deg, #0F172A 0%, #1E293B 100%);
        color: #FFFFFF;
        position: relative;
        overflow: hidden;
    }
    .auth-sidebar::after {
        content: '';
        position: absolute;
        bottom: -60px;
        right: -60px;
        width: 240px;
        height: 240px;
        background: radial-gradient(circle, rgba(244, 123, 32, 0.35) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    /* Input Group Customization */
    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--brand-dark);
        margin-bottom: 6px;
    }

    .input-group-custom {
        border-radius: 12px;
        overflow: hidden;
        border: 1.5px solid var(--border-color);
        transition: all 0.25s ease;
        background-color: #FFFFFF;
    }

    .input-group-custom:focus-within {
        border-color: var(--brand-primary);
        box-shadow: 0 0 0 4px rgba(244, 123, 32, 0.15);
    }

    .input-group-custom .input-group-text {
        background-color: transparent;
        border: none;
        color: var(--brand-muted);
        padding-left: 14px;
        padding-right: 8px;
    }

    .input-group-custom .form-control {
        border: none;
        padding: 12px 14px 12px 6px;
        font-size: 0.95rem;
        box-shadow: none;
        color: var(--brand-dark);
    }

    .input-group-custom .btn-toggle-pwd {
        border: none;
        background: transparent;
        color: var(--brand-muted);
        padding-right: 14px;
        cursor: pointer;
    }
    .input-group-custom .btn-toggle-pwd:hover {
        color: var(--brand-primary);
    }

    /* Submit Button */
    .btn-auth-submit {
        background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);
        color: #FFFFFF;
        font-weight: 700;
        padding: 12px 24px;
        border-radius: 12px;
        border: none;
        font-size: 1rem;
        box-shadow: 0 6px 18px rgba(244, 123, 32, 0.35);
        transition: all 0.25s ease;
    }
    .btn-auth-submit:hover {
        color: #FFFFFF;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(244, 123, 32, 0.45);
    }

    /* Portal Feature Pill */
    .portal-feature {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.07);
        border: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 0.85rem;
        color: #E2E8F0;
    }

    .form-check-input:checked {
        background-color: var(--brand-primary);
        border-color: var(--brand-primary);
    }
</style>
</head>
<body>
    
<div class="login-page-wrapper py-5 d-flex align-items-center min-vh-100">
    <div class="container py-lg-3 d-flex justify-content-center align-items-center">
        <!-- Main Auth Card -->
        <div class="auth-card w-100" style="max-width: 480px;">
            <div class="p-4 p-md-5 bg-white d-flex flex-column justify-content-center">
                <div class="mb-4">
                    <h4 class="fw-bold text-dark mb-1">Masuk ke Akun Anda</h4>
                    <p class="text-muted small mb-0">Gunakan username dan kata sandi yang telah Anda daftarkan sebelumnya.</p>
                </div>

                    <!-- Alert Notifikasi Error Validasi Laravel -->
                    @if (session('success'))
                        <div class="alert alert-success border-0 rounded-3 small py-2 px-3 mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 rounded-3 small py-2 px-3 mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-circle-fill fs-5 flex-shrink-0"></i>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <form action="{{ route('login.authenticate') }}" method="POST">
                        @csrf

                        <!-- 1. Username -->
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <div class="input-group-custom d-flex align-items-center @error('username') border-danger @enderror">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" 
                                       name="username" 
                                       class="form-control" 
                                       placeholder="Masukkan username Anda" 
                                       value="{{ old('username') }}" 
                                       required 
                                       autofocus>
                            </div>
                        </div>

                        <!-- 2. Password -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0">Password <span class="text-danger">*</span></label>
                            </div>
                            <div class="input-group-custom d-flex align-items-center mt-1 @error('password') border-danger @enderror">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" 
                                       name="password" 
                                       id="loginPassword" 
                                       class="form-control" 
                                       placeholder="Masukkan kata sandi" 
                                       required>
                                <button type="button" 
                                        class="btn-toggle-pwd" 
                                        onclick="togglePasswordVisibility('loginPassword', this)" 
                                        title="Lihat Kata Sandi">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 5. Tombol Submit Login -->
                        <button type="submit" class="btn btn-auth-submit w-100 d-flex align-items-center justify-content-center gap-2 mb-4">
                            <span>Masuk ke Portal</span>
                            <i class="bi bi-box-arrow-in-right"></i>
                        </button>

                        <div class="text-center pt-2 border-top">
                            <p class="text-muted small mb-0">
                                Gunakan akun yang telah disediakan oleh administrator.
                            </p>
                        </div>
                    </form>
                </div>


        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, btnElement) {
        const input = document.getElementById(inputId);
        const icon = btnElement.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

</script>
</body>
</html>
