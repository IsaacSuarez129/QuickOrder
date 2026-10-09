<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Order - Iniciar Sesión</title>
    <!-- Bootstrap 5 CSS para diseño responsivo y limpio -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">🍕 Quick Order</h2>
            <p class="text-muted">Ingresa tus datos para acceder</p>
        </div>

        <!-- Alerta para mensajes de error -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Formulario con validación visual (needs-validation) -->
        <form action="../../controladores/ControladorAutenticacion.php?accion=login" method="POST" class="needs-validation" novalidate>
            
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario o Correo</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required>
                <div class="invalid-feedback">
                    Por favor ingresa tu usuario.
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="invalid-feedback">
                    Por favor ingresa tu contraseña.
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>

    <!-- Script de Bootstrap para validación visual en cliente -->
    <script>
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>