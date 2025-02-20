<?php require_once '../includes/header.php'; ?>

<body>
    <!--Barra Navegación-->
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="login.php"><img src="../img/blog.png" alt="blog" width="50px" height="50px">BGU</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="login.php">Inicio</a>
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

    <!--Título encabezado-->
    <div class="encabezadoinicio">
        <h1>Login de Usuarios</h1>
    </div>

    <div class="formularioLogin">
        <form class="formulario" action="" method="POST"><!--Enviamos los datos-->
            <!-- Imagen superior -->
            <img src="../img/formuser.png" alt="Login">
            <div class="mb-3">
                <!--Usuario-->
                <label for="usuario" class="form-label"> Usuario</label>
                <div class="d-flex col">
                    <!--Insertamos el nombre usuario aterior si hubiese-->
                    <input class="form-control" name="usuario" type="text" placeholder="Nombre usuario" aria-label="default input example">
                    <img class="border rounded bg-body-secondary" src="../img/user_login.png" width="40px" height="40px" />
                </div>
                <?php /*if (empty($_POST['usuario'])) {
                    echo $msgresultadoCampo;
                } */ ?> <!-- Mensaje de resultado campos vacíos-->
            </div>

            <!--Password-->
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <div class="d-flex col">
                    <input class="form-control" name="password" type="password">
                    <img class="border rounded bg-body-secondary" src="../img/contrasena_login.png" width="40px" height="40px" />
                </div>
                <?php /*if (empty($_POST['password'])) {
                    echo $msgresultadoCampo;
                } */ ?> <!-- Mensaje de resultado campos vacíos-->
            </div>
            <button name="btningresar" type="submit" class="btn btn-primary">INICIAR SESION</button>
        </form>
    </div>
</body>
<?php require_once '../includes/footer.php'; ?>