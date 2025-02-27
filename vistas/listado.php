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
        <h1>Lista Categorias</h1>
    </div>

    <!--Mensaje Bienvenida-->
    <div class="container d-flex justify-content-end">
        <p class="fs-5 me-2">Bienvenido, </p>
        <p class="fs-5 fw-bold text-uppercase mx-2"><img class="me-2" src="fotos/<?php echo $_SESSION['usuario']['avatar']; ?>" alt="avatar" width="30px" height="30px"><?php echo $_SESSION['usuario']['nombre']; ?></p>
    </div>

    <!--Menú para los diferentes roles (user admin) botones-->
    <div class="container mt-5 justify-content-center">
        <div class="d-flex flex-row mb-5 justify-content-evenly">

            <!--Definimos si el rol es usuario o admin-->

            <?php if ($_SESSION['usuario']['rol'] == 'user') { ?>
                <!----------------USUARIO---------------->

                <!--Agregar Entrada-->
                <a class="navbar-brand mx-2 fs-5" href="index.php?accion=agregarentrada">Agregar Nueva Entrada<img class="mx-2" width="40" height="40" src="img/nuevaentrada.png" alt="agregar entrada"></a>

                <!--Salir login-->
                <a class="navbar-brand mx-2 fs-5" href="index.php?accion=logout">Cerrar Sesión<img class="mx-2" src="img/exit.png" alt="salir" width="40" height="40"></a>

            <?php } elseif ($_SESSION['usuario']['rol'] == 'admin') { ?>
                <!----------------ADMIN---------------->

                <!--Salir login-->
                <a class="navbar-brand mx-2" href="index.php?accion=logout">Cerrar Sesión<img class="mx-2" src="img/exit.png" alt="salir" width="40" height="40"></a>

                <!--Agregar Entrada-->
                <a class="navbar-brand mx-2 fs-5" href="index.php?accion=agregarentrada">Agregar Nueva Entrada<img class="mx-2" width="40" height="40" src="img/nuevaentrada.png" alt="agregar entrada"></a>

            <?php } ?>

        </div>
    </div>

    <!--Creamos la tabla para mostrar las Entradas-->
    <h2 class="text-center w-100"> Tabla de tus Entradas <img src="img/tablaentradas.jpg" alt="tablaentradas" width="100" height="80"> Almacenadas</h2>

    <!--Mostramos los posibles errores-->
    <?php
    if (!empty($parametros["mensajes"])) {
        // Mostramos los mensajes procedentes del controlador que se hayn generado
        foreach ($parametros["mensajes"] as $mensaje) : ?>
            <div class="alert mx-auto w-50 text-center alert-<?= $mensaje["tipo"] ?>"><?= $mensaje["mensaje"] ?></div>
    <?php endforeach;
    }
    ?>
    <div class="table-container mx-4">
        <table class="table table-striped text-center align-middle">

            <!--Mostramos resultados-->
            <tr class="fs-5">
                <th>Categoría</th>
                <th>Título</th>
                <th><?php if ($_SESSION['usuario']['rol'] == 'admin') {
                        echo "Email";
                    }  ?></th>
                <th>Imagen</th>
                <th>Descripción</th>
                <th>Fecha</th>
                <th class="text-center" colspan="3">Operaciones</th>
            </tr>

            <!--Mostramos los datos traidos-->
            <?php foreach ($parametros['datos'] as $d) { ?>
                <!--Fila-->
                <tr>
                    <td><?php echo ucfirst($d['categoria']) ?></td>
                    <td><?php echo ucfirst($d['titulo']) ?></td>
                    <td><?php if ($_SESSION['usuario']['rol'] == 'admin') {
                            echo ucfirst($d['email']);
                        }  ?></td>
                    <?php if ($d['imagen'] != NULL && file_exists("fotos/" . $d['imagen'])) { ?>
                        <td><img src="fotos/<?php echo $d['imagen'] ?>" alt="" width="50" height="50"></td>
                    <?php } else { ?>
                        <td>---</td>
                    <?php } ?>
                    <td><?php echo ucfirst($d['descripcion']) ?></td>

                    <!--Convertimos la fecha a formato d/m/a-->
                    <td><?php echo date("d/m/Y", strtotime($d['fecha'])); ?></td>

                    <!--Enviamos a actentrada.php o delentrada, mediante GET, el id del registro que deseamos editar o eliminar-->
                    <td class="col-12 col-sm-4 my-2">
                        <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">

                            <!--BOTON EDITAR-->
                            <a class="btn btn-primary w-80 w-md-auto" href="index.php?accion=actentrada&id=<?php echo $d['ident'] ?>">Editar</a>

                            <!--BOTON ELIMINAR-->
                            <a class="btn btn-danger w-80 w-md-auto"
                                data-bs-toggle="modal"
                                data-bs-target="#confirmarEliminarModal"
                                data-id="<?php echo $d['ident']; ?>"
                                data-titulo="<?php echo $d['titulo']; ?>">
                                Eliminar
                            </a>

                            <!--BOTON DETALLE-->
                            <a class="btn btn-success w-80 w-md-auto" href="index.php?accion=detalleentrada&id=<?php echo $d['ident'] ?>">Detalle</a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <!-- Incluir la modal de confirmación -->
    <?php include 'includes/modaleliminar.php'; ?>
    <script src="js/modalconfirmareliminar.js"></script>

</body>
<?php require_once 'includes/footer.php'; ?>

</html>