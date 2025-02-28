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
        <h1>Detalle Entrada</h1>
    </div>

    <!--Mensaje Bienvenida-->
    <div class="container d-flex justify-content-end">
        <p class="fs-5 me-2">Bienvenido, </p>
        <p class="fs-5 fw-bold text-uppercase mx-2"><img class="me-2" src="fotos/<?php echo $_SESSION['usuario']['avatar']; ?>" alt="avatar" width="30px" height="30px"><?php echo $_SESSION['usuario']['nombre']; ?></p>
    </div>

    <!--Enlaces listado-->
    <div class="d-flex flex-row mb-5 justify-content-evenly">
        <!--Atrás-->
        <a class="navbar-brand mx-2 fs-5" href="index.php?accion=listado">Atrás<img class="mx-2" src="img/flechaAtras.png" alt="atras" width="40" height="40"></a>

        <!--Salir login-->
        <a class="navbar-brand mx-2 fs-5" href="index.php?accion=logout">Cerrar Sesión<img class="mx-2" src="img/exit.png" alt="salir" width="40" height="40"></a>
    </div>

    <!--Mostramos los posibles errores-->
    <?php
    if (!empty($parametros["mensajes"])) {
        // Mostramos los mensajes procedentes del controlador que se hayn generado
        foreach ($parametros["mensajes"] as $mensaje) : ?>
            <div class="alert mx-auto w-50 text-center alert-<?= $mensaje["tipo"] ?>"><?= $mensaje["mensaje"] ?></div>
    <?php endforeach;
    }
    ?>

    <div class="detalle-container my-3">

        <div class="titulo-container">
            <?php //var_dump($parametros); 
            ?>
            <h1 class="text-danger">Detalle <img src="img/entradaBlog.jpg" alt="entradablog" width="150" height="100">Entrada Blog </h1>
        </div>

        <h3><b>Título:</b></h3>
        <p class="detalle-texto"><?php echo htmlspecialchars($parametros['datos']['titulo']); ?></p>

        <h3><b>Categoría:</b></h3>
        <p class="detalle-texto"><?php echo htmlspecialchars($parametros['datos']['categoria']); ?></p>

        <h3><b>Nombre Usuario:</b></h3>
        <p class="detalle-texto"><?php echo htmlspecialchars($parametros['datos']['nombre']); ?></p>

        <h3><b>Avatar:</b></h3>
        <img class="avatar" src="fotos/<?php echo htmlspecialchars($parametros['datos']['avatar']); ?>" alt="Avatar del usuario" width="60" height="60">

        <h3><b>Imagen:</b></h3>
        <img class="entrada-imagen" src="fotos/<?php echo htmlspecialchars($parametros['datos']['imagen']); ?>" alt="Imagen de la entrada" width="70" height="70">

        <h3><b>Descripcion</b></h3>
        <p class="detalle-texto"><?php echo nl2br(htmlspecialchars($parametros['datos']['descripcion'])); ?></p>

        <h3><b>Fecha:</b></h3>
        <p class="detalle-texto"><?php echo htmlspecialchars($parametros['datos']['fecha']); ?></p>

    </div>

    <?php //var_dump($parametros); 
    ?>
</body>
<?php require_once 'includes/footer.php'; ?>

</html>