<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickOrder</title>
    <link rel="stylesheet" href="publico/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <header>
        <div class="login_header">
            <h1>Bienvenido a QuickOrder</h1>
        </div>
    </header>

    <main class="contenedor contenedor-login sombra">
        <section>
            <div>
            <h2>Iniciar Sesion</h2>

            <div class="img_login centrar">
                <img src="publico/img/logo_usr.svg" alt="icono1">
            </div>
        </div>
        </section>

        <section>
            <form action="controladores/ControladorAutenticacion.php" class="formulario centrar" id="form_login" method="POST">
                <label for="inp_usr">Ingresar Usuario:</label><br>
                <input type="text" name="usuarios" placeholder="Usuario" id="inp_usr">
                <br>

                <label for="inp_con">Ingresar Contraseña:</label><br>
                <!-- Agregado minlength="8" para permitir caracteres especiales y exigir mínimo 8 -->
                <input type="password" name="contrasena" placeholder="Contraseña" id="inp_con" minlength="8">

                <br>
                <button class="btn centrar" type="submit" id="btn_enviar">Ingresar</button>
            </form>
        </section>

    </main>

    <?php include "vistas/plantillas/footer.php";?>

    <script>
    $(document).ready(function(){
      $("#btn_enviar").click(function(e){
        e.preventDefault();

        let nombre = $("#inp_usr").val().trim();
        let contrasena = $("#inp_con").val().trim();

        if(nombre === ""){
          alert("Este campo no puede estar vacío");
          $("#inp_usr").focus();
          return false;
        }

        if(contrasena === ""){
          alert("Este campo no puede estar vacío");
          $("#inp_con").focus();
          return false;
        }

        // Validación de mínimo 8 caracteres antes de enviar
        if(contrasena.length < 8){
          alert("La contraseña debe tener al menos 8 caracteres");
          $("#inp_con").focus();
          return false;
        }

        $("#form_login")[0].submit();
      });
    });
  </script>
</body>
</html>