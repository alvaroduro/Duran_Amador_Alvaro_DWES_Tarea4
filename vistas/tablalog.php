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
        <h1>Crear tabla Logs</h1>
    </div>

    <!--Guardamos el parametro recibido por url-->
    <?php if (isset($_GET['id'])) {
        $id = $_GET['id'];
        //var_dump($id);
    } ?>

    <div class="container d-flex justify-content-end">
        <p class="fs-5 me-2">Bienvenido, </p>
        <p class="fs-5 fw-bold text-uppercase mx-2"><img class="me-2" src="fotos/<?php echo $_SESSION['usuario']['avatar']; ?>" alt="avatar" width="30px" height="30px"><?php echo $_SESSION['usuario']['nombre']; ?></p>
    </div>

    <!--Enlaces listado-->
    <div class="d-flex flex-row mb-5 justify-content-evenly">

        <!--Atrás-->
        <a class="navbar-brand mx-2 fs-5" href="index.php?accion=listadopagOrdenado">Atrás<img class="mx-2" src="img/flechaAtras.png" alt="atras" width="40" height="40"></a>

        <!--Salir login-->
        <a class="navbar-brand mx-2 fs-5" href="index.php?accion=logout">Cerrar Sesión<img class="mx-2" src="img/exit.png" alt="salir" width="40" height="40"></a>
    </div>
</body>
<!--Mensajes guardados-->
<?php foreach ($parametros["mensajes"] as $mensaje) : ?>
    <div class="alert alert-<?= $mensaje["tipo"] ?>"><?= $mensaje["mensaje"] ?></div>
<?php endforeach; ?>
<?php require_once 'includes/footer.php'; ?>

</html>