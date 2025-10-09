<?php
/**
 * Página de login - Página principal del sistema
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Si ya está logueado, redirigir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Procesar intentos de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validaciones de seguridad
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'El email es requerido';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no es válido';
    }
    
    if (empty($password)) {
        $errors[] = 'La contraseña es requerida';
    }
    
    // Verificar intentos de login fallidos
    $login_attempts = $_SESSION['login_attempts'] ?? 0;
    $last_attempt = $_SESSION['last_login_attempt'] ?? 0;
    $time_since_last = time() - $last_attempt;
    
    if ($login_attempts >= 5 && $time_since_last < 300) { // 5 minutos de bloqueo
        $remaining_time = 300 - $time_since_last;
        $errors[] = "Demasiados intentos fallidos. Intenta de nuevo en " . ceil($remaining_time / 60) . " minutos.";
    }
    
    if (empty($errors)) {
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            // Resetear intentos de login
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_login_attempt']);
            
            // Configurar sesión segura
            if ($remember) {
                ini_set('session.cookie_lifetime', 86400 * 30); // 30 días
            } else {
                ini_set('session.cookie_lifetime', 0); // Sesión de navegador
            }
            
            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);
            
            header('Location: index.php');
            exit();
        } else {
            // Incrementar intentos de login
            $_SESSION['login_attempts'] = $login_attempts + 1;
            $_SESSION['last_login_attempt'] = time();
            $error = $result['message'];
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

// Mostrar mensaje de éxito si viene de registro
if (isset($_GET['registered'])) {
    $success = '¡Registro exitoso! Ya puedes iniciar sesión.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Iniciar Sesión - <?php echo Config::SITE_NAME; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
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
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">
            <h1><i class="fas fa-tools text-primary"></i> AlquiVenta</h1>
            <p class="subtitle">Sistema de Venta y Alquiler de Maquinaria</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           placeholder="tu@email.com" required autocomplete="email">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Tu contraseña" required autocomplete="current-password">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    Recordarme
                </label>
            </div>

            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                </button>
            </div>
        </form>

        <div class="text-center">
            <p class="text-muted mb-3">¿No tienes cuenta?</p>
            <a href="register.php" class="btn btn-outline-primary">
                <i class="fas fa-user-plus mr-2"></i>Crear Cuenta
            </a>
        </div>

        <div class="text-center mt-3">
            <a href="forgot-password.php" class="text-muted">
                <i class="fas fa-key mr-1"></i>¿Olvidaste tu contraseña?
            </a>
        </div>

        <div class="security-info">
            <i class="fas fa-shield-alt mr-2"></i>
            <strong>Información de Seguridad:</strong><br>
            Tu sesión es segura y encriptada. No compartas tus credenciales.
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        $('#togglePassword').on('click', function() {
            const passwordField = $('#password');
            const icon = $(this).find('i');
            
            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Form validation
        $('#loginForm').on('submit', function(e) {
            const email = $('#email').val().trim();
            const password = $('#password').val();
            
            if (!email || !password) {
                e.preventDefault();
                alert('Por favor complete todos los campos');
                return false;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                alert('Por favor ingrese un email válido');
                return false;
            }
        });

        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Auto-focus on email field
        $('#email').focus();
    </script>
</body>
</html>