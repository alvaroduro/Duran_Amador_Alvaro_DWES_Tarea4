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
        <h1>Acceso Usuarios</h1>
    </div>

    <!--FORMULARIO-->
    <div class="formularioLogin">
        <form class="formulario" action="index.php?accion=validarLogin" method="POST"><!--Enviamos los datos-->

            <!-- Imagen superior form-->
            <img src="img/formuser.png" alt="Login">
            <div class="mb-3">

                <!--Usuario-->
                <label for="email" class="form-label"> Email</label>
                <div class="d-flex col">

                    <!--Insertamos el nombre usuario aterior si hubiese-->
                    <input class="form-control" name="email" type="text" placeholder="Email" aria-label="default input example" value="<?= isset($_COOKIE['usuario']) ? $_COOKIE['usuario'] : '' ?>">
                    <img class="border rounded bg-body-secondary" src="img/user_login.png" width="40px" height="40px" />
                </div>
            </div>

            <!--Password-->
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <div class="d-flex col">
                    <input class="form-control" name="password" type="password">
                    <img class="border rounded bg-body-secondary" src="img/contrasena_login.png" width="40px" height="40px" />
                </div>

            </div>

            <!-- Opciones de sesión -->
            <!-- Recordar Usuario -->
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="recordar" id="recordar"
                    <?= isset($_COOKIE['usuario']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="recordar">Recordar usuario</label>
            </div>

            <!-- Mantener Sesion Abierta -->
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="mantenerSesion" id="mantenerSesion">
                <label class="form-check-label" for="mantenerSesion">Mantener sesión activa</label>
            </div>

            <!--Mostramos los posibles errores-->
            <?php
            if (!empty($parametros["mensajes"])) {
                // Mostramos los mensajes procedentes del controlador que se hayn generado
                foreach ($parametros["mensajes"] as $mensaje) : ?>
                    <div class="alert alert-<?= $mensaje["tipo"] ?>"><?= $mensaje["mensaje"] ?></div>
            <?php endforeach;
            }
            ?>

            <!--BTN Iniciar sesion-->
            <button name="btningresar" type="submit" class="btn btn-primary">INICIAR SESION</button>
        </form>
    </div>
</body>
<?php require_once 'includes/footer.php'; ?>

</html>