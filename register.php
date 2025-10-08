<?php
/**
 * Página de registro de usuarios
 */

session_start();
require_once 'includes/auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Si ya está logueado, redirigir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    $errors = [];
    
    if (empty($nombre)) {
        $errors[] = 'El nombre es requerido';
    } elseif (strlen($nombre) < 2) {
        $errors[] = 'El nombre debe tener al menos 2 caracteres';
    }
    
    if (empty($apellido)) {
        $errors[] = 'El apellido es requerido';
    } elseif (strlen($apellido) < 2) {
        $errors[] = 'El apellido debe tener al menos 2 caracteres';
    }
    
    if (empty($email)) {
        $errors[] = 'El email es requerido';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no es válido';
    }
    
    if (empty($password)) {
        $errors[] = 'La contraseña es requerida';
    } elseif (strlen($password) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors[] = 'La contraseña debe contener al menos una letra minúscula, una mayúscula y un número';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Las contraseñas no coinciden';
    }
    
    if (!empty($telefono) && !preg_match('/^[\d\s\-\+\(\)]+$/', $telefono)) {
        $errors[] = 'El teléfono contiene caracteres no válidos';
    }
    
    if (empty($errors)) {
        $result = $auth->register($nombre, $apellido, $email, $password, $telefono, $direccion);
        
        if ($result['success']) {
            $success = '¡Registro exitoso! Ya puedes iniciar sesión.';
        } else {
            $error = $result['message'];
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Registro - <?php echo Config::SITE_NAME; ?></title>
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
        }
        .auth-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: #667eea;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background: #5a6fd8;
        }
        .alert {
            border-radius: 5px;
        }
        .password-strength {
            font-size: 12px;
            margin-top: 5px;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="text-center mb-4">
            <h2 class="text-primary mb-3">
                <i class="fas fa-user-plus mr-2"></i>Registro
            </h2>
            <p class="text-muted">Crea tu cuenta en AlquiVenta</p>
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
            <div class="text-center">
                <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
            </div>
        <?php else: ?>
            <form method="POST" id="registerForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="apellido" class="form-label">Apellido *</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" 
                               value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                           value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <textarea class="form-control" id="direccion" name="direccion" rows="2"><?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña *</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div id="passwordStrength" class="password-strength"></div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label class="form-check-label" for="terms">
                        Acepto los <a href="#" class="text-primary">términos y condiciones</a>
                    </label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus mr-2"></i>Registrarse
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <div class="text-center mt-4">
            <p class="text-muted">
                ¿Ya tienes cuenta? 
                <a href="login.php" class="text-primary">Iniciar Sesión</a>
            </p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de contraseña en tiempo real
        $('#password').on('input', function() {
            const password = $(this).val();
            const strength = checkPasswordStrength(password);
            const strengthDiv = $('#passwordStrength');
            
            strengthDiv.removeClass('strength-weak strength-medium strength-strong');
            
            if (password.length === 0) {
                strengthDiv.text('');
            } else if (strength < 3) {
                strengthDiv.addClass('strength-weak').text('Contraseña débil');
            } else if (strength < 5) {
                strengthDiv.addClass('strength-medium').text('Contraseña media');
            } else {
                strengthDiv.addClass('strength-strong').text('Contraseña fuerte');
            }
        });

        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            return strength;
        }

        // Validación del formulario
        $('#registerForm').on('submit', function(e) {
            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                return false;
            }
        });
    </script>
</body>
</html>
