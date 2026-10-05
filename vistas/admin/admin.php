<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin</title>
  <link rel="stylesheet" href="../../publico/css/style.css">
</head>
<body>
  <header>
    <div class="cont_header">
      <h1>Panel de Administración</h1>
    </div>
  </header>

  <main class="contenedor cont_pantallas">
        
        <!-- 1. Tarjetas de Métricas Rápidas -->
        <section class="admin-grid">
            <div class="card-stat sombra">
                <h3>Ventas de Hoy</h3>
                <p>$4,850.00 MXN</p>
            </div>
            <div class="card-stat sombra">
                <h3>Comandas Atendidas</h3>
                <p>38</p>
            </div>
            <div class="card-stat sombra">
                <h3>Mesas Activas</h3>
                <p>6 / 12</p>
            </div>
            <div class="card-stat sombra">
                <h3>Personal de Turno</h3>
                <p>5</p>
            </div>
        </section>

        <!-- 2. Accesos Rápidos a Módulos del Sistema -->
        <h2 class="mod_sistem">Módulos del Sistema</h2>
        <section class="modulos-grid">
            <a href="../menu/menu.php" class="btn-modulo sombra">
                <span>🍽️</span>
                <span>Gestionar Menú</span>
            </a>
            <a href="../usuarios/usuarios.php" class="btn-modulo sombra">
                <span>👥</span>
                <span>Usuarios / Empleados</span>
            </a>
            <a href="../reportes/reportes.php" class="btn-modulo sombra">
                <span>📊</span>
                <span>Reportes y Ventas</span>
            </a>
            <a href="../configuracion/configuracion.php" class="btn-modulo sombra">
                <span>⚙️</span>
                <span>Configuración</span>
            </a>
        </section>

        <!-- 3. Registro de Actividad Reciente -->
        <section class="tabla-contenedor sombra">
            <h2 class="mod_sistem">Últimas Comandas Cerradas</h2>
            <table class="tabla-general">
                <thead>
                    <tr>
                        <th># Folio</th>
                        <th>Mesa</th>
                        <th>Mesero / Atendió</th>
                        <th>Total</th>
                        <th>Hora</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#0104</td>
                        <td>Mesa 3</td>
                        <td>Carlos V.</td>
                        <td>$340.00</td>
                        <td>14:25</td>
                        <td><span class="estado-completado">Pagado</span></td>
                    </tr>
                    <tr>
                        <td>#0103</td>
                        <td>Mesa 1</td>
                        <td>Ana G.</td>
                        <td>$520.00</td>
                        <td>14:10</td>
                        <td><span class="estado-completado">Pagado</span></td>
                    </tr>
                    <tr>
                        <td>#0102</td>
                        <td>Mesa 5</td>
                        <td>Carlos V.</td>
                        <td>$180.00</td>
                        <td>13:45</td>
                        <td><span class="estado-completado">Pagado</span></td>
                    </tr>
                </tbody>
            </table>
        </section>

    </main>

  <?php include "../../vistas/plantillas/footer.php";?>
</body>
</html>