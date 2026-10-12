<?php
require_once "../../controladores/Controlador_seguridad.php";
// Solo el rol de admin tiene permiso aquí
verificarAcceso(['admin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin | QuickOrder</title>
  <link rel="stylesheet" href="../../publico/css/style.css">
</head>
<body class="admin-body">
  <header>
    <div class="cont_header admin-header-nav">
      <h1 class="admin-header-title">Panel de Administración</h1>

      <!-- Desplegable del usuario -->
      <div class="qo-user-dropdown">
        <button type="button" class="qo-role-btn" id="btnDropdownUsuario" aria-expanded="false" aria-haspopup="true">
          <span class="qo-role-dot">●</span>
          <span class="qo-role-text"><?= htmlspecialchars($_SESSION['rol'] ?? 'Usuario') ?></span>
          <span class="qo-role-arrow">▼</span>
        </button>

        <div class="qo-dropdown-menu" id="menuUsuario" hidden>
          <div class="qo-dropdown-header">
            <small>Conectado como</small>
            <strong><?= htmlspecialchars($_SESSION['usuario_activo'] ?? $_SESSION['rol'] ?? 'Usuario') ?></strong>
          </div>
          <hr class="qo-dropdown-divider">
          <a href="../../controladores/Controlador_terminar_sesion.php" id="btnCerrarSesion" class="qo-dropdown-item qo-item-logout">
            🚪 Cerrar sesión
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Contenedor Maestro Dashboard -->
  <div class="dashboard-layout">

    <!-- Menú lateral izquierdo -->
    <aside class="dashboard-sidebar">
      <ul class="sidebar-menu">
        <li>
          <button type="button" class="sidebar-btn activo" data-target="vista-resumen">
            📊 - Resumen General
          </button>
        </li>
        <li>
          <button type="button" class="sidebar-btn" data-target="vista-menu">
            🍽️ - Menú y Catálogo
          </button>
        </li>
        <li>
          <button type="button" class="sidebar-btn" data-target="vista-usuarios">
            👥 - Usuarios y Empleados
          </button>
        </li>
        <li>
          <button type="button" class="sidebar-btn" data-target="vista-reportes">
            📈 - Reportes y Ventas
          </button>
        </li>
        <li>
          <button type="button" class="sidebar-btn" data-target="vista-comandas">
            🧾 - Comandas Cerradas
          </button>
        </li>
        <li>
          <button type="button" class="sidebar-btn" data-target="vista-configuracion">
            ⚙️ - Configuración
          </button>
        </li>
      </ul>
    </aside>

    <!-- Área de contenido a la derecha -->
    <main class="dashboard-content">

      <!-- 1. VISTA: Resumen General (Solo Métricas) -->
      <section id="vista-resumen" class="modulo-seccion activo">
        <h2 class="content-header-title">Métricas del Turno</h2>
        <div class="admin-grid">
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
        </div>
      </section>

      <!-- 2. VISTA: Gestión del Menú -->
      <section id="vista-menu" class="modulo-seccion">
        <div class="content-card">
          <h2 class="content-header-title">Catálogo de Productos y Menú</h2>
          <p class="content-description">Administra las opciones de platillos, bebidas y postres disponibles para las comandas.</p>
          <div class="btn-group-row">
            <a href="../menu/menu.php" class="qo-btn qo-btn-primary">
              🍽️ Abrir Catálogo Completo
            </a>
          </div>
        </div>
      </section>

      <!-- 3. VISTA: Usuarios y Empleados -->
      <section id="vista-usuarios" class="modulo-seccion">
        <div class="content-card">
          <h2 class="content-header-title">Control de Usuarios y Empleados</h2>
          <p class="content-description">Gestiona las credenciales de acceso para chefs, meseros y administradores.</p>
          <div class="btn-group-row">
            <a href="../usuarios/usuarios.php" class="qo-btn qo-btn-primary">
              👥 Abrir Módulo de Personal
            </a>
          </div>
        </div>
      </section>

      <!-- 4. VISTA: Reportes y Ventas -->
      <section id="vista-reportes" class="modulo-seccion">
        <div class="content-card">
          <h2 class="content-header-title">Reportes Financieros y Ventas</h2>
          <p class="content-description">Consulta el balance de ventas de caja, consumos por mesa y estadísticas por platillo.</p>
          <div class="btn-group-row">
            <a href="../cocina/cocina.php" class="qo-btn qo-btn-light">
              📊 Ver Reportes
            </a>
          </div>
        </div>
      </section>

      <!-- 5. VISTA: Comandas Cerradas -->
      <section id="vista-comandas" class="modulo-seccion">
        <div class="content-card">
          <h2 class="content-header-title">Últimas Comandas Cerradas</h2>
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
        </div>
      </section>

      <!-- 6. VISTA: Configuración -->
      <section id="vista-configuracion" class="modulo-seccion">
        <div class="content-card">
          <h2 class="content-header-title">Configuración del Restaurante</h2>
          <p class="content-description">Ajusta los nombres de las áreas, impresoras de cocina y parámetros de cobro.</p>
          <div class="btn-group-row">
            <a href="../configuracion/configuracion.php" class="qo-btn qo-btn-light">
              ⚙️ Entrar a Configuración
            </a>
          </div>
        </div>
      </section>

    </main>
  </div>

  <?php include "../../vistas/plantillas/footer.php"; ?>

  <!-- Script del menú de usuario -->
  <script src="../../publico/js/dropdown_usuario.js"></script>

  <!-- Script para alternar las vistas del Dashboard -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const botonesSidebar = document.querySelectorAll(".sidebar-btn");
      const secciones = document.querySelectorAll(".modulo-seccion");

      botonesSidebar.forEach(btn => {
        btn.addEventListener("click", () => {
          botonesSidebar.forEach(b => b.classList.remove("activo"));
          btn.classList.add("activo");

          secciones.forEach(sec => sec.classList.remove("activo"));

          const seccionDestino = document.getElementById(btn.dataset.target);
          if (seccionDestino) {
            seccionDestino.classList.add("activo");
          }
        });
      });
    });
  </script>
</body>
</html>