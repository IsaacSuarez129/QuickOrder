<?php
// 1. Control de acceso: solo entran admin y cajero
require_once "../../controladores/Controlador_seguridad.php";
verificarAcceso(['admin', 'cajero']);

// 2. Determinar si el usuario conectado tiene rol de administrador
$esAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
$enlaceAdmin = $esAdmin ? '../admin/admin.php' : 'menu.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo del menú | QuickOrder</title>
    <link rel="stylesheet" href="../../publico/css/menu.css">
</head>
<body>
    <div class="qo-layout">
        <main class="qo-main">
            <!-- Barra superior -->
            <header class="qo-topbar">
                <div>
                    <span class="qo-eyebrow">ADMINISTRACIÓN</span>
                    <h1>Catálogo del menú</h1>
                </div>
                <span class="qo-role">● <?= htmlspecialchars($_SESSION['rol'] ?? 'Usuario') ?></span>
            </header>

            <!-- Acciones y navegación -->
            <section class="qo-intro">
                <div>
                    <h2>Productos</h2>
                    <p>Administra los platillos y bebidas disponibles para los pedidos.</p>
                </div>
                <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
                    <!-- Botón para regresar a Caja -->
                    <a href="../caja/caja.php" class="qo-btn qo-btn-light" style="text-decoration: none;">⬅ Volver a Caja</a>

                    <!-- Botón restringido: Lleva a Admin si es admin, o recarga menu.php si es cajero -->
                    <a href="<?= $enlaceAdmin ?>" class="qo-btn qo-btn-light" style="text-decoration: none;">
                        ⚙️ Panel de Admin
                    </a>

                    <!-- Botón para agregar producto -->
                    <button class="qo-btn qo-btn-primary" id="btnNuevo">＋ Agregar producto</button>
                </div>
            </section>

            <!-- Métricas superiores -->
            <section class="qo-stats" aria-label="Resumen del catálogo">
                <article class="qo-stat">
                    <span class="qo-stat-icon red">▤</span>
                    <div>
                        <small>Total de productos</small>
                        <strong id="totalProductos">-</strong>
                    </div>
                </article>
                <article class="qo-stat">
                    <span class="qo-stat-icon green">✓</span>
                    <div>
                        <small>Disponibles</small>
                        <strong id="totalDisponibles">-</strong>
                    </div>
                </article>
                <article class="qo-stat">
                    <span class="qo-stat-icon gold">◷</span>
                    <div>
                        <small>No disponibles</small>
                        <strong id="totalNoDisponibles">-</strong>
                    </div>
                </article>
            </section>

            <!-- Tabla y filtros -->
            <section class="qo-card">
                <div class="qo-toolbar">
                    <div class="qo-search">
                        <span>⌕</span>
                        <input id="buscarProducto" type="search" placeholder="Buscar por nombre..." aria-label="Buscar producto">
                    </div>
                    <select id="filtroCategoria" aria-label="Filtrar por categoría">
                        <option value="">Todas las categorías</option>
                        <option>Platillos</option>
                        <option>Bebidas</option>
                        <option>Postres</option>
                        <option>Entradas</option>
                    </select>
                    <select id="filtroEstado" aria-label="Filtrar por disponibilidad">
                        <option value="">Todos los estados</option>
                        <option value="disponible">Disponible</option>
                        <option value="inactivo">No disponible</option>
                    </select>
                </div>
                
                <div class="qo-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>PRODUCTO</th>
                                <th>CATEGORÍA</th>
                                <th>PRECIO</th>
                                <th>DISPONIBILIDAD</th>
                                <th class="qo-actions-heading">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody id="tablaProductos"></tbody>
                    </table>
                </div>

                <div class="qo-table-footer">
                    <span id="textoResultados">Cargando productos...</span>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal para Agregar / Editar Producto -->
    <div class="qo-modal-backdrop" id="modalProducto" hidden>
        <section class="qo-modal" role="dialog" aria-modal="true" aria-labelledby="tituloModal">
            <div class="qo-modal-header">
                <div>
                    <span class="qo-eyebrow">CATÁLOGO</span>
                    <h2 id="tituloModal">Agregar producto</h2>
                </div>
                <button class="qo-close" id="btnCerrar" type="button" aria-label="Cerrar">×</button>
            </div>
            <form id="formProducto">
                <input type="hidden" id="productoId">
                
                <label for="nombreProducto">Nombre del producto <span>*</span></label>
                <input id="nombreProducto" maxlength="80" required placeholder="Ej. Hamburguesa clásica">
                
                <label for="descripcionProducto">Descripción</label>
                <textarea id="descripcionProducto" maxlength="240" rows="3" placeholder="Describe brevemente el producto"></textarea>
                
                <div class="qo-form-row">
                    <div>
                        <label for="precioProducto">Precio (MXN) <span>*</span></label>
                        <input id="precioProducto" type="number" min="0.01" step="0.01" required placeholder="0.00">
                    </div>
                    <div>
                        <label for="categoriaProducto">Categoría <span>*</span></label>
                        <select id="categoriaProducto" required>
                            <option value="">Selecciona</option>
                            <option>Platillos</option>
                            <option>Bebidas</option>
                            <option>Postres</option>
                            <option>Entradas</option>
                        </select>
                    </div>
                </div>

                <label class="qo-check">
                    <input type="checkbox" id="disponibleProducto" checked> Producto disponible para nuevos pedidos
                </label>

                <div class="qo-modal-actions">
                    <button type="button" class="qo-btn qo-btn-light" id="btnCancelar">Cancelar</button>
                    <button type="submit" class="qo-btn qo-btn-primary">Guardar producto</button>
                </div>
            </form>
        </section>
    </div>

    <!-- Script que interactúa con la base de datos -->
    <script src="../../publico/js/menu.js?v=3"></script>
</body>
</html>