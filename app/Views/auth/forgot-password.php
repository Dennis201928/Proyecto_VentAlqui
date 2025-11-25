<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recuperar Contraseña - AlquiVenta</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .auth-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }
        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #333;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0;
        }
        .logo .subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn {
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 500;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-outline-secondary {
            border: 1px solid #ced4da;
            color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
        }
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        .alert {
            border-radius: 8px;
            padding: 12px 15px;
        }
        .input-group {
            display: flex;
            flex-wrap: nowrap;
        }
        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #ced4da;
            border-right: none;
            border-radius: 8px 0 0 8px;
            padding: 12px 15px;
            display: flex;
            align-items: center;
        }
        .input-group .form-control {
            border-left: none;
            border-right: 1px solid #ced4da;
            border-radius: 0;
            flex: 1;
        }
        .input-group .form-control:focus {
            border-right-color: #667eea;
        }
        .input-group.has-button .form-control {
            border-right: none;
        }
        .input-group .btn {
            border: 1px solid #ced4da;
            border-left: none;
            border-radius: 0 8px 8px 0;
            padding: 12px 15px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 45px;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        .input-group .btn:hover {
            background: #f8f9fa;
            border-color: #ced4da;
            color: #495057;
        }
        .input-group .btn:focus {
            box-shadow: none;
            outline: none;
        }
        .input-group .btn i {
            font-size: 14px;
        }
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }
        .input-group:focus-within .form-control {
            border-color: #667eea;
        }
        .input-group:focus-within .btn {
            border-color: #667eea;
        }
        .security-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            color: #004085;
            padding: 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-top: 20px;
        }
        .security-info i {
            color: #667eea;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .password-strength {
            margin-top: 5px;
            font-size: 0.8rem;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        .d-grid {
            display: grid;
        }
    </style>
</head>
<body>
    <?php 
    use App\Core\Config;
    $baseUrl = Config::SITE_URL ?? '/Proyecto_VentAlqui/public';
    $user_found = isset($_GET['email']) && !empty($_GET['email']);
    $error = $_GET['error'] ?? '';
    $success = $_GET['success'] ?? '';
    ?>
    <div class="auth-container">
        <div class="logo">
            <h1><i class="fas fa-key text-primary"></i> Recuperar Contraseña</h1>
            <p class="subtitle">
                <?php if ($user_found): ?>
                    Usuario encontrado. Ingresa tu nueva contraseña
                <?php else: ?>
                    Ingresa tu email para cambiar tu contraseña
                <?php endif; ?>
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($success)): ?>
        <form method="POST" action="<?php echo $baseUrl; ?>/recuperar-contrasena" id="forgotPasswordForm">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>" 
                           placeholder="tu@email.com" required autocomplete="email"
                           <?php echo $user_found ? 'readonly' : ''; ?>>
                </div>
            </div>

            <?php if ($user_found): ?>
            <div class="mb-3">
                <label for="new_password" class="form-label">Nueva Contraseña</label>
                <div class="input-group has-button">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="new_password" name="new_password" 
                           placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength" id="passwordStrength"></div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                <div class="input-group has-button">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Repite tu nueva contraseña" required autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div id="passwordMatch" class="password-strength"></div>
            </div>
            <?php endif; ?>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary">
                    <?php if ($user_found): ?>
                        <i class="fas fa-save mr-2"></i>Cambiar Contraseña
                    <?php else: ?>
                        <i class="fas fa-search mr-2"></i>Buscar Usuario
                    <?php endif; ?>
                </button>
            </div>
        </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="<?php echo $baseUrl; ?>/login">
                <i class="fas fa-arrow-left mr-2"></i>Volver al Login
            </a>
        </div>

        <div class="security-info">
            <i class="fas fa-shield-alt mr-2"></i>
            <strong>Información de Seguridad:</strong><br>
            El enlace de recuperación será válido por 1 hora. Si no recibes el email, revisa tu carpeta de spam.
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#toggleNewPassword').on('click', function() {
                const passwordField = $('#new_password');
                const icon = $(this).find('i');
                
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $('#toggleConfirmPassword').on('click', function() {
                const passwordField = $('#confirm_password');
                const icon = $(this).find('i');
                
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $('#new_password').on('input', function() {
                const password = $(this).val();
                const strengthDiv = $('#passwordStrength');
                
                if (password.length === 0) {
                    strengthDiv.html('');
                    return;
                }
                
                let strength = 0;
                let strengthText = '';
                let strengthClass = '';
                
                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                if (strength < 3) {
                    strengthText = 'Contraseña débil';
                    strengthClass = 'strength-weak';
                } else if (strength < 5) {
                    strengthText = 'Contraseña media';
                    strengthClass = 'strength-medium';
                } else {
                    strengthText = 'Contraseña fuerte';
                    strengthClass = 'strength-strong';
                }
                
                strengthDiv.html('<span class="' + strengthClass + '">' + strengthText + '</span>');
            });

            $('#confirm_password').on('input', function() {
                const password = $('#new_password').val();
                const confirmPassword = $(this).val();
                const matchDiv = $('#passwordMatch');
                
                if (confirmPassword.length === 0) {
                    matchDiv.html('');
                    return;
                }
                
                if (password === confirmPassword) {
                    matchDiv.html('<span class="strength-strong">Las contraseñas coinciden</span>');
                } else {
                    matchDiv.html('<span class="strength-weak">Las contraseñas no coinciden</span>');
                }
            });
        });
    </script>
</body>
</html>
