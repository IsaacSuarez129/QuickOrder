<?php 
require_once "../../controladores/Controlador_seguridad.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cocina</title>
  <link rel="stylesheet" href="../../publico/css/style.css">
</head>
<body>
  <header>
    <div class="cont_header">
      <h1>Panel de Cocina</h1>
    </div>
  </header>

  <main class="contenedor cont_pantallas" >

          <a href="../../controladores/Controlador_terminar_sesion.php">Cerrar Sesión</a>
        <!-- Barra de herramientas: Filtros y refrescar -->
        <section class="cocina-toolbar sombra">
            <div class="cocina-filtros">
                <button type="button" class="btn-filtro activo">Todos (4)</button>
                <button type="button" class="btn-filtro">Pendientes (2)</button>
                <button type="button" class="btn-filtro">En Preparación (2)</button>
            </div>
            <div>
                <button type="button" class="btn-refrescar">🔄 Actualizar Pedidos</button>
            </div>
        </section>

        <!-- Cuadrícula de comandas / pedidos en preparación -->
        <section class="pedidos-grid">

            <!-- Tarjeta 1: Demorada / En espera -->
            <div class="comanda-card sombra">
                <div class="comanda-header">
                    <h3>Mesa 3 <small class= "num_comanda">(#104)</small></h3>
                    <span class="tiempo-badge demorado">⏱️ 18 min</span>
                </div>
                <div class="comanda-cuerpo">
                    <ul class="lista-platillos">
                        <li class="item-platillo">
                            <span class="platillo-nombre">2x Hamburguesa Clásica</span>
                            <span class="platillo-obs">⚠️ Una sin cebolla ni pepinillos</span>
                        </li>
                        <li class="item-platillo">
                            <span class="platillo-nombre">1x Orden de Papas Gajo</span>
                        </li>
                        <li class="item-platillo">
                            <span class="platillo-nombre">1x Refresco Cola</span>
                        </li>
                    </ul>
                </div>
                <div class="comanda-footer">
                    <button type="button" class="btn-estado btn-preparar">Empezar Preparación</button>
                </div>
            </div>

            <!-- Tarjeta 2: En preparación -->
            <div class="comanda-card en-proceso sombra">
                <div class="comanda-header">
                    <h3>Mesa 5 <small class= "num_comanda">(#105)</small></h3>
                    <span class="tiempo-badge">⏱️ 8 min</span>
                </div>
                <div class="comanda-cuerpo">
                    <ul class="lista-platillos">
                        <li class="item-platillo">
                            <span class="platillo-nombre">1x Tacos de Pastor (Orden)</span>
                            <span class="platillo-obs">⚠️ Con todo (piña, cilantro y cebolla)</span>
                        </li>
                        <li class="item-platillo">
                            <span class="platillo-nombre">1x Agua de Horchata 1L</span>
                        </li>
                    </ul>
                </div>
                <div class="comanda-footer">
                    <button type="button" class="btn-estado btn-listo">Marcar Listo</button>
                </div>
            </div>

            <!-- Tarjeta 3: En preparación -->
            <div class="comanda-card en-proceso sombra">
                <div class="comanda-header">
                    <h3>Para Llevar <small class="num_comanda">(#106)</small></h3>
                    <span class="tiempo-badge">⏱️ 5 min</span>
                </div>
                <div class="comanda-cuerpo">
                    <ul class="lista-platillos">
                        <li class="item-platillo">
                            <span class="platillo-nombre">1x Pizza Mediana Peperoni</span>
                        </li>
                        <li class="item-platillo">
                            <span class="platillo-nombre">6x Alitas BBQ</span>
                            <span class="platillo-obs">⚠️ Salsa aparte</span>
                        </li>
                    </ul>
                </div>
                <div class="comanda-footer">
                    <button type="button" class="btn-estado btn-listo">Marcar Listo</button>
                </div>
            </div>

            <!-- Tarjeta 4: Recién entrada -->
            <div class="comanda-card sombra">
                <div class="comanda-header">
                    <h3>Mesa 1 <small class="num_comanda">(#107)</small></h3>
                    <span class="tiempo-badge">⏱️ 2 min</span>
                </div>
                <div class="comanda-cuerpo">
                    <ul class="lista-platillos">
                        <li class="item-platillo">
                            <span class="platillo-nombre">1x Ensalada César con Pollo</span>
                        </li>
                        <li class="item-platillo">
                            <span class="platillo-nombre">1x Té Helado</span>
                        </li>
                    </ul>
                </div>
                <div class="comanda-footer">
                    <button type="button" class="btn-estado btn-preparar">Empezar Preparación</button>
                </div>
            </div>

        </section>

    </main>

  <?php include "../../vistas/plantillas/footer.php";?>
</body>
</html>