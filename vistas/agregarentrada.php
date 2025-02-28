<?php require_once 'includes/header.php'; ?>

<body>
    <!--Barra Navegación-->
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><img src="img/blog.png" alt="blog" width="50px" height="50px">BGU</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contacto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Registrate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true">Proximamente</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!--TITULO encabezado-->
    <div class="encabezadoinicio">
        <h1>Agregar Nueva Entrada Blog</h1>
    </div>

    <div class="container d-flex justify-content-end">
        <p class="fs-5 me-2">Bienvenido, </p>
        <p class="fs-5 fw-bold text-uppercase mx-2"><img class="me-2" src="fotos/<?php echo $_SESSION['usuario']['avatar']; ?>" alt="avatar" width="30px" height="30px"><?php echo $_SESSION['usuario']['nombre']; ?></p>
    </div>

    <!--Enlaces listado-->
    <div class="d-flex flex-row mb-5 justify-content-evenly">
        <!--Atrás-->
        <a class="navbar-brand mx-2 fs-5" href="index.php?accion=listadopag">Atrás<img class="mx-2" src="img/flechaAtras.png" alt="atras" width="40" height="40"></a>

        <!--Salir login-->
        <a class="navbar-brand mx-2 fs-5" href="index.php?accion=logout">Cerrar Sesión<img class="mx-2" src="img/exit.png" alt="salir" width="40" height="40"></a>
    </div>

    <div class="container d-flex justify-content-center align-items-center my-5">
        <div class="card p-4 shadow-lg" style="width: 600px;">
            <h3 class="text-center mb-4">Agregar Entrada</h3>

            <?php
            // Guardamos los datos para la ventana modal si no hay errores 
            if (!empty($parametros["mensajes"])) {
                if ($parametros["mensajes"][0]['tipo'] === 'success') {
                    //Almacenamos variables
                    if ($parametros["datos"]["categoria"] == 1) {
                        $categoria = "accesorios";
                    } else {
                        $categoria = "consola";
                    }
                    $titulo = $parametros["datos"]["titulo"];
                    $fecha = $parametros["datos"]["fecha"];
                    $descripcion = $parametros["datos"]["descripcion"];
                }

                // Mostramos la ventana modal si hay datos
                if (!empty($categoria) && !empty($titulo) && !empty($fecha) && !empty($descripcion)) {
                    // Mostramos una ventana modal con los datos del libro introducido al clicar un botón
                    require 'includes/modalagregarentrada.php';
                }
            }  ?>


            <!--Mensajes guardados-->
            <?php foreach ($parametros["mensajes"] as $mensaje) : ?>
                <div class="alert alert-<?= $mensaje["tipo"] ?>"><?= $mensaje["mensaje"] ?></div>
            <?php endforeach; ?>

            <!--Formulario para agregar una entrada de blog-->
            <form action="index.php?accion=agregarentrada" method="POST" enctype="multipart/form-data">

                <!-- Categoría -->
                <div class="mb-3">
                    <label for="categoria" class="form-label"><b>Categoría</b></label>
                    <select class="form-select" id="categoria" name="categoria">
                        <option value="accesorios">Accesorios</option>
                        <option value="consolas">Consolas</option>
                        <?php echo mostrar_error($errores, "categoria"); ?>
                    </select>
                </div>

                <!-- Título -->
                <div class="mb-3">
                    <label for="titulo" class="form-label"><b>Título</b></label>
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?= $parametros["datos"]["titulo"] ?>">
                    <?php echo mostrar_error($errores, "titulo"); ?>
                </div>

                <!-- Imagen -->
                <div class="mb-3">
                    <label for="imagen" class="form-label"><b>Imagen</b></label>
                    <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                    <!--Si la imagen no es null mostramos la ultima imagen cargada-->
                    <?php if (!empty($parametros["datos"]["imagen"])) {
                        echo '<div class="mt-2">';
                        echo '<label>Última imagen cargada:</label><br>';
                        echo '<img src="fotos/' . $parametros["datos"]["imagen"] . '" alt="Imagen cargada" width="70" height="80">';
                        echo '</div>';
                    } ?>
                    <?php echo mostrar_error($errores, "imagen"); ?>
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="descripcion" class="form-label"><b>Descripción</b></label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" style="resize: none;">
                    <?php echo $parametros["datos"]["descripcion"]; ?>
                    </textarea>
                    <?php echo mostrar_error($errores, "descripcion"); ?>
                </div>

                <!-- Incluyendo CKEditor desde CDN -->
                <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

                <script>
                    // Inicializar CKEditor en el textarea con id="descripcion"
                    ClassicEditor
                        .create(document.querySelector('#descripcion'))
                        .catch(error => {
                            console.error(error);
                        });
                </script>

                <!-- Fecha -->
                <!-- Fecha (Oculta) -->
                <div class="mb-3">
                    <!--<label for="descripcion" class="form-label">Fecha</label>-->
                    <input type="hidden" id="fecha" name="fecha" value="<?php echo date('Y-m-d H:i:s'); ?>">
                    <?php echo mostrar_error($errores, "fecha"); ?>
                </div>

                <!-- Botón de Enviar -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary" name="submit">Guardar</button>
                </div>

            </form>
        </div>
    </div>

</body>
<?php require_once 'includes/footer.php'; ?>

</html>