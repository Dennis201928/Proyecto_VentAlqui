<?php
use App\Core\Config;
$baseUrl = Config::SITE_URL;
$user_found = isset($_GET['email']) && !empty($_GET['email']);
$user_data = null;
if ($user_found) {
    // En una implementación real, buscarías el usuario aquí
    // Por ahora, solo mostramos el formulario
}
?>
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        border: 2px solid #e9ecef;
        padding: 15px 20px;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 15px 30px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    .btn-outline-primary {
        border: 2px solid #667eea;
        color: #667eea;
        padding: 15px 30px;
        border-radius: 8px;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    .btn-outline-primary:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }
    .alert {
        border-radius: 8px;
        border: none;
        padding: 15px 20px;
    }
    .input-group-text {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-right: none;
    }
    .input-group .form-control {
        border-left: none;
    }
    .input-group:focus-within .input-group-text {
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
    .user-info {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
    }
    .user-info .user-name {
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }
    .user-info .user-email {
        color: #666;
        font-size: 0.9rem;
    }
    .password-strength {
        margin-top: 5px;
        font-size: 0.8rem;
    }
    .strength-weak { color: #dc3545; }
    .strength-medium { color: #ffc107; }
    .strength-strong { color: #28a745; }
</style>

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

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle mr-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if (!isset($success) || !$success): ?>
    <form method="POST" action="<?php echo $baseUrl; ?>/recuperar-contrasena" id="forgotPasswordForm">
        <div class="mb-4">
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
        <div class="mb-4">
            <label for="new_password" class="form-label">Nueva Contraseña</label>
            <div class="input-group">
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

        <div class="mb-4">
            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
            <div class="input-group">
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

        <div class="d-grid mb-4">
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

<script>
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
        
        strengthDiv.html(`<span class="${strengthClass}">${strengthText}</span>`);
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
</script>

