<?php
require_once "../../controladores/Controlador_seguridad.php";
verificarAcceso(['mesero', 'admin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestionar comandas | QuickOrder</title>
  <link rel="stylesheet" href="../../publico/css/style.css">
  <link rel="stylesheet" href="../../publico/css/comandas.css">
</head>
<body>
  <header><div class="cont_header"><h1>Gestión de comandas</h1></div></header>
  <main class="contenedor gestion-comandas">
    <div class="gestion-barra">
      <div>
        <p>Consulta, edita o cancela pedidos registrados.</p>
        <small>Las comandas en preparación requieren autorización del administrador para editarse o cancelarse.</small>
      </div>
      <div class="gestion-acciones">
        <a class="qo-btn qo-btn-light" href="caja.php">← Volver a Caja</a>
        <a class="qo-btn qo-btn-light" href="nueva_comanda.php">+ Nueva comanda</a>
        <button type="button" class="btn-actualizar" id="btnActualizar">Actualizar</button>
      </div>
    </div>
    <div class="filtros-comandas">
      <input id="buscarComanda" type="search" placeholder="Buscar por folio, mesa o mesero…" aria-label="Buscar comandas">
      <select id="filtroEstado" aria-label="Filtrar por estado">
        <option value="todos">Todos los estados</option>
        <option>Pendiente</option><option>En preparación</option><option>Listo</option><option>Entregado</option><option>Cancelado</option>
      </select>
    </div>
    <div id="mensajeComandas" class="mensaje-comandas" role="status">Cargando comandas…</div>
    <div class="tabla-scroll">
      <table class="tabla-gestion">
        <thead><tr><th>Folio</th><th>Pedido</th><th>Mesero</th><th>Fecha</th><th>Estado</th><th>Total</th><th>Acciones</th></tr></thead>
        <tbody id="cuerpoComandas"></tbody>
      </table>
    </div>
  </main>

  <div class="modal-comanda" id="modalDetalle" hidden>
    <section class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="tituloModalDetalle">
      <button class="modal-cerrar" type="button" data-cerrar="modalDetalle" aria-label="Cerrar">×</button>
      <h2 id="tituloModalDetalle">Editar comanda</h2>
      <p id="avisoEstado" class="aviso-estado"></p>
      <form id="formEditarComanda">
        <input type="hidden" id="idComandaEditar">
        <div id="filasProductos"></div>
        <button type="button" class="btn-secundario" id="btnAgregarProducto">+ Agregar producto</button>
        <div class="total-edicion">Nuevo total: <strong id="totalEdicion">$0.00</strong></div>
        <div class="modal-botones"><button type="button" class="btn-secundario" data-cerrar="modalDetalle">Volver</button><button type="submit" class="btn-principal">Guardar cambios</button></div>
      </form>
    </section>
  </div>

  <div class="modal-comanda" id="modalAdmin" hidden>
    <section class="modal-contenido modal-admin-contenido" role="dialog" aria-modal="true" aria-labelledby="tituloModalAdmin">
      <button class="modal-cerrar" type="button" data-cerrar="modalAdmin" aria-label="Cerrar">×</button>
      <h2 id="tituloModalAdmin">Autorización del administrador</h2>
      <p>Esta comanda ya está en preparación. Un administrador debe autorizar la operación.</p>
      <form id="formAdmin">
        <label for="usuarioAdmin">Usuario administrador</label><input id="usuarioAdmin" name="usuario" autocomplete="username" required>
        <label for="contrasenaAdmin">Contraseña</label><input id="contrasenaAdmin" name="contrasena" type="password" autocomplete="current-password" required>
        <p id="errorAdmin" class="error-admin" role="alert"></p>
        <div class="modal-botones"><button type="button" class="btn-secundario" data-cerrar="modalAdmin">Cancelar</button><button type="submit" class="btn-principal">Autorizar y continuar</button></div>
      </form>
    </section>
  </div>

  <script src="../../publico/js/gestionar_comandas.js"></script>
</body>
</html>
