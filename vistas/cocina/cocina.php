<?php
require_once "../../controladores/Controlador_seguridad.php";
// El chef solo puede acceder al Monitor de Cocina; el administrador también puede
// cargar esta vista dentro de su dashboard.
verificarAcceso(['chef', 'admin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monitor de Cocina - QuickOrder</title>
  <link rel="stylesheet" href="../../publico/css/style.css">
  <link rel="stylesheet" href="../../publico/css/cocina.css">
</head>
<body>
  <header>
    <div class="cont_header">
      <h1>Panel de Cocina</h1>
    </div>
  </header>

  <main class="cocina-contenedor">

    <!-- Usuario autenticado -->
    <div class="qo-acciones-admin barra-usuario-derecha">
      <div class="qo-user-dropdown">
        <button type="button" class="qo-role-btn" id="btnDropdownUsuario" aria-expanded="false">
          <span class="qo-role-dot">●</span>
          <span class="qo-role-text"><?= htmlspecialchars($_SESSION['rol'] ?? 'Chef') ?></span>
          <span class="qo-role-arrow">▼</span>
        </button>

        <div class="qo-dropdown-menu" id="menuUsuario" hidden>
          <div class="qo-dropdown-header">
            <small>Conectado como</small>
            <strong><?= htmlspecialchars($_SESSION['usuario_activo'] ?? 'Chef') ?></strong>
          </div>
          <hr class="qo-dropdown-divider">
          <a href="../../controladores/Controlador_terminar_sesion.php" id="btnCerrarSesion" class="qo-dropdown-item qo-item-logout">
            🚪 Cerrar sesión
          </a>
        </div>
      </div>
    </div>

    <!-- Barra de Filtros -->
    <section class="cocina-toolbar">
      <div class="cocina-filtros">
        <button type="button" class="btn-filtro activo" data-filtro="todos">Todos (<span id="cont-todos">0</span>)</button>
        <button type="button" class="btn-filtro" data-filtro="pendiente">Pendientes (<span id="cont-pendientes">0</span>)</button>
        <button type="button" class="btn-filtro" data-filtro="en_preparacion">En Preparación (<span id="cont-preparacion">0</span>)</button>
      </div>
      <div>
        <button type="button" class="btn-refrescar" id="btnRefrescarPedidos">🔄 Actualizar Pedidos</button>
      </div>
    </section>

    <!-- Contenedor donde se inyectan las tarjetas dinámicas -->
    <section class="pedidos-grid" id="contenedor-comandas">
      <!-- Se llena por JavaScript -->
    </section>

  </main>

  <?php include "../../vistas/plantillas/footer.php"; ?>

  <script src="../../publico/js/dropdown_usuario.js"></script>
  <script src="../../publico/js/cocina_kanban.js"></script>
</body>
</html>