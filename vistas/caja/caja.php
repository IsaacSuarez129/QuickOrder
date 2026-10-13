<?php
require_once "../../controladores/Controlador_seguridad.php";
// Tiene permiso el cajero y el admin
verificarAcceso(['mesero', 'admin']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Caja</title>
  <link rel="stylesheet" href="../../publico/css/style.css">
</head>
<body>
  <header>
    <div class="cont_header">
      <h1>Panel de Caja</h1>
    </div>
  </header>

  <main class="contenedor cont_pantallas">

    <!-- Barra de acciones: Ver Menú a la izquierda y Dropdown a la derecha en la misma línea -->
    <div class="qo-acciones-admin btns_caja-menu">
      <a href="../menu/menu.php" class="qo-btn qo-btn-light">Ver Menú</a>
      <a href="nueva_comanda.php" class="qo-btn qo-btn-light">+ Nueva comanda</a>
      <a href="gestionar_comandas.php" class="qo-btn qo-btn-light">Gestionar comandas</a>

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

    <!-- Gestión manual de mesas ocupadas -->
    <section class="bloque-blanco sombra mesas-ocupadas-panel">
      <div class="mesas-panel-cabecera">
        <div>
          <h2>Mesas ocupadas</h2>
          <p>Libera la mesa cuando los clientes se hayan retirado y no tenga comandas activas.</p>
        </div>
        <button type="button" class="btn-actualizar-mesas" id="btnActualizarMesas">Actualizar mesas</button>
      </div>
      <p id="mensajeMesas" class="mensaje-mesas" role="status">Cargando mesas ocupadas…</p>
      <div class="tabla-scroll">
        <table class="tabla-caja">
          <thead>
            <tr><th>Mesa</th><th>Capacidad</th><th>Comandas activas</th><th>Acción</th></tr>
          </thead>
          <tbody id="cuerpoMesasOcupadas"></tbody>
        </table>
      </div>
    </section>

    <!-- 1. Métricas rápidas del turno -->
    <section class="caja-resumen">
      <div class="card-caja sombra">
        <h3>Total en Caja</h3>
        <p>$3,420.00</p>
      </div>
      <div class="card-caja sombra">
        <h3>Cobros Realizados</h3>
        <p>19</p>
      </div>
      <div class="card-caja sombra">
        <h3>Comandas Pendientes</h3>
        <p>3</p>
      </div>
      <div class="card-caja sombra">
        <h3>Fondo Inicial</h3>
        <p>$1,000.00</p>
      </div>
    </section>

    <!-- 2. Área de trabajo: Comandas pendientes y panel de cobro -->
    <section class="panel-doble">
      <!-- Tabla de comandas listas para pagar -->
      <div class="bloque-blanco sombra">
        <h2 class="izq">Cuentas por Cobrar</h2>
        <table class="tabla-caja">
          <thead>
            <tr>
              <th># Comanda</th>
              <th>Mesa</th>
              <th>Mesero</th>
              <th>Total</th>
              <th>Método</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>#105</strong></td>
              <td>Mesa 4</td>
              <td>Carlos V.</td>
              <td><strong>$320.00</strong></td>
              <td>
                <select class="select-pago">
                  <option value="efectivo">Efectivo</option>
                  <option value="tarjeta">Tarjeta</option>
                  <option value="transferencia">Transferencia</option>
                </select>
              </td>
              <td>
                <button type="button" class="btn-cobrar">Cobrar</button>
              </td>
            </tr>
            <tr>
              <td><strong>#106</strong></td>
              <td>Mesa 2</td>
              <td>Ana G.</td>
              <td><strong>$175.50</strong></td>
              <td>
                <select class="select-pago">
                  <option value="tarjeta">Tarjeta</option>
                  <option value="efectivo">Efectivo</option>
                  <option value="transferencia">Transferencia</option>
                </select>
              </td>
              <td>
                <button type="button" class="btn-cobrar">Cobrar</button>
              </td>
            </tr>
            <tr>
              <td><strong>#107</strong></td>
              <td>Para Llevar</td>
              <td>Carlos V.</td>
              <td><strong>$95.00</strong></td>
              <td>
                <select class="select-pago">
                  <option value="efectivo">Efectivo</option>
                  <option value="tarjeta">Tarjeta</option>
                </select>
              </td>
              <td>
                <button type="button" class="btn-cobrar">Cobrar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="bloque-blanco sombra">
        <h2>Corte de caja</h2>
        <form onsubmit="event.preventDefault();">
          <div class="campo-formulario">
            <label for="monto_total">Usuario:</label>
            <input type="number" id="monto_total" placeholder="0.00" value="320.00" readonly>
          </div>
          <div class="campo-formulario">
            <label for="monto_recibido">Efectivo Recibido ($):</label>
            <input type="number" id="monto_recibido" placeholder="Ej. 500">
          </div>
          <div class="campo-formulario">
            <label for="cambio_devolver">Dinero por Tarjeta Recibido ($):</label>
            <input type="text" id="cambio_devolver" placeholder="$0.00" readonly>
          </div>
          <button type="button" class="btn-corte">Hacer Corte de Turno</button>
        </form>
      </div>
    </section>

  </main>

  <?php include "../../vistas/plantillas/footer.php"; ?>

  <!-- Script para la interacción del dropdown y confirmación -->
  <script src="../../publico/js/dropdown_usuario.js"></script>
  <script src="../../publico/js/mesas_caja.js"></script>
</body>
</html>