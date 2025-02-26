<!--Clase para controlar la logica del programa y enlazar VC-->
<?php
require_once 'modelos/modelo.php'; // Incluir la clase modelo antes de usarla
class controlador
{

    //Definimos el att modelo de la 'clase modelo' y ser a traves el cual
    //Podremos acceder a los datos y las operaciones de la BD desde el controlador
    private $modelo;

    //$mensajes se utiliza para almacenar los sms generados en las tareas,
    //que sean luego transmitidos a la vista 
    private $mensajes;

    /**
     * Constructor que crea automaticamente un objeto modelo en el controlador
     * Inicializa los mensajes en vacio
     */

    public function __construct()
    {
        $this->modelo = new modelo(); //Crea un objeto tipo modelo
        $this->mensajes = []; //Array con los mensajes
    }

    /**
     * Método que envia al usuario a la pag inicio y si le asigna el titulo dinamicamente
     */
    public function index()
    {
        $parametros = ["tituloventana" => "Blog Usuarios con PHP y PDO"];

        //Mostramos la pag inicio 
        include_once 'vistas/login.php';
    }

    /**
     * Funcion para iniciar sesion en caso de que no haya una iniciada
     */
    function iniciarSesion()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Funcion para validar el login del usuario. Mediante el metodo POST rcibimos una 
     * respuesta y la evaluamos si exite, y guardamos en variables los resultados.
     */
    public function validarLogin()
    {
        // Si se ha pulsado el botón Iniciar Sesion...
        if (isset($_POST) && isset($_POST['btningresar'])) {

            // Si los campos estan vacíos
            if (empty($_POST['email']) || empty($_POST['password'])) {
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => "Hay campos vacíos"
                ];

                // Almacenamos los mensajes de error o acierto
                $parametros["mensajes"] =  $this->mensajes;

                include_once 'vistas/login.php';
            } else {

                // Recogemos los datos del login si no están vacíos
                $email = $_POST['email'];
                $password =  ($_POST['password']); //Encriptamos el pass

                // Iniciamos sesión
                $this->iniciarSesion();

                // Guardamos en una variable los datos obtenidos del metodo de modelo
                $resultadomodelo = $this->modelo->login($email, $password);

                // Si la consulta se realizó correctamente transferimos los datos obtenidos
                // de la consulta del modelo ($resultModelo["datos"]) a nuestro array parámetros
                // ($parametros["datos"]), que será el que le pasaremos a la vista para visualizarlos

                // Si se ha obtenido el resultado correcto
                if ($resultadomodelo['correcto']) {


                    //Definimos el mensaje para el alert de la vista de que todo fue correctamente
                    $this->mensajes[] = [
                        "tipo" => "success",
                        "mensaje" => "Creedenciales correctas, Bienvenido!!"
                    ];

                    // Vemos los checkbox
                    $recordar = isset($_POST['recordar']);
                    $mantenerSesion = isset($_POST['mantenerSesion']);
                    //var_dump($email);

                    // Si el usuario quiere recordar su email
                    if ($recordar) {
                        setcookie("usuario", $_SESSION['usuario']['email'], time() + (86400 * 30), "/"); // Cookie por 30 días
                    } else {
                        setcookie("usuario", "", time() - 3600, "/"); // Borrar cookie
                    }

                    // Si el usuario quiere mantener la sesión activa
                    if ($mantenerSesion) {
                        setcookie("mantenerSesion", $_SESSION['usuario']['nombre'], time() + (86400 * 30), "/"); // Sesión por 30 días
                    }

                    //Relizamos el listado de los Entradas
                    $this->listado();

                    // Mostramos la vista del listado
                    include_once 'vistas/listado.php';

                    // Si las creedenciales no son correctas
                } else {
                    $this->mensajes[] = [
                        "tipo" => "danger",
                        "mensaje" => "Creedenciales Incorrectas"
                    ];

                    $parametros["mensajes"] =  $this->mensajes;

                    include_once 'vistas/login.php';
                }
            }
        }
    }

    /**
     * Funcion que muestra el listado de las categorias de cada usuario
     */
    public function listado()
    {

        // Iniciamos sesión
        $this->iniciarSesion();

        $this->compruebasesion(); //Comprobamos si hay sesion de usuario iniciada

        //Almacenamos en el array los valores mostrados en la vista
        $parametros = [
            "tituloventana" => "Listado Entradas",
            "datos" => NULL,
            "mensajes" => []
        ];

        //Realizamos la consulta y almacenamos en nuestra variable
        $resultsModelo = $this->modelo->listado($_SESSION['usuario']['iduser']);

        /**Si la consulta esta bien, transferimos los datos a la ariable en la parte de datos
         * del array parametros->datos
         */
        if ($resultsModelo["correcto"]) {
            $parametros['datos'] = $resultsModelo['datos'];

            //Definimos un mensaje para el alert en la vista
            $this->mensajes[] = [
                "tipo" => "success",
                "mensaje" => "El listado se realizó correctamente"
            ];
        } else {
            //definimos el mensaje por si falla 
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "El listado no pudo realizarse correctamente!! <br/>({$resultsModelo['error']})"
            ];
            $parametros['mensajes'] = $this->mensajes;

            include_once 'vistas/login.php';
        }

        //Asignamos al campo mensajes del array de parametros del att mensaje, que recoge
        // como finalizo la operacion
        $parametros['mensajes'] = $this->mensajes;

        //incluimos la vista a mostrar
        include_once 'vistas/listado.php';
    }

    /**
     * Función para eliminar las sesiones y las cookies del usuario
     * Mostramos por último la vista de login de nuevo
     */
    public function logout()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Si hay cookies de sesión activas, las eliminamos
        /*if (isset($_COOKIE['usuario'])) {
            setcookie('usuario', '', time() - 3600, '/'); // Expira la cookie
        }*/

        session_unset(); //Liberamos las variables de la sesión
        // Destruir la sesión
        session_destroy();

        // Redirigir al login
        //incluimos la vista a mostrar
        include_once 'vistas/login.php';
        exit();
    }

    /**
     * Funcion para comprobar si la sesion se ha iniciado correctamente
     * En caso contrario realizaremos la fx logout y saldremos al login
     */
    public function compruebasesion()
    {

        if (!isset($_SESSION['usuario'])) {
            $this->logout();
        }
    }

    public function agregarentrada()
    {
        // Iniciamos sesión
        $this->iniciarSesion();

        var_dump($_SESSION['usuario']);
        /**
         * Script que muestra en una tabla los valores enviados por el usuario a través 
         * del formulario utilizando el método POST
         */

        // Definimos e inicializamos el array de errores
        // Definimos e inicializamos el array de errores y las variables asociadas a cada campo
        $errores = [];
        $categoria = "";
        $titulo = "";
        $descripcion = "";
        $fecha = "";

        // Función que muestra el mensaje de error bajo el campo que no ha superado
        // el proceso de validación
        function mostrar_error($errores, $campo)
        {
            $alert = "";
            if (isset($errores[$campo]) && (!empty($campo))) {
                $alert = '<div class="alert alert-danger" style="margin-top:5px;">' . $errores[$campo] . '</div>';
            }
            return $alert;
        }

        // Visualización de las variables obtenidas mediante el formulario en modal
        function valoresfrm($categoria, $titulo, $fecha, $descripcion)
        {
            echo "<h4>Datos de la entrada <b>" . $titulo . "</b> obtenidos mediante el formulario</h4><br/>";
            echo "<strong>Categoría: </strong>" . $categoria . "<br/>";
            echo "<strong>Titulo: </strong>" . $titulo . "<br/>";
            echo "<strong>Fecha Publicación: </strong>" . $fecha . "<br/>";
            echo "<strong>Descripcion: </strong>" . $descripcion . "<br/>";
            echo "<strong>Imagen: </strong>Imagen recibida<br/>";
        }


        // Si se ha pulsado el  botón guardar...
        if (isset($_POST) && !empty($_POST) && isset($_POST['submit'])) {
            $parametros = "";

            //Validamos Campos

            //Campo idCategoria
            if (
                !empty($_POST["categoria"])
                && ($_POST["categoria"] == "accesorios" || $_POST["categoria"] == "consolas")
            ) {

                //Dfinimos el idCategoria
                if ($categoria == "accesorios") {
                    $categoria = 1;
                } else {
                    $categoria = 2;
                }
                //echo  "categoria: <b>" . $categoria . "</b><br/>";
            } else {
                $errores["categoria"] = "Debes elegir una categoría válido";
            }

            //Campo Título
            if (
                !empty($_POST["titulo"]) &&
                (strlen($_POST["titulo"]) < 100)
            ) {
                //Satinizamos
                $titulo = htmlspecialchars(trim($_POST['titulo']));
                //echo  "Título: <b>" . $titulo . "</b><br/>";
            } else {
                $errores["titulo"] = "No puede estar vacío/No puede contener más de 20 caracteres";
            }

            //Campo Fecha Publicación
            if (!empty($_POST["fecha"])) {
                //Guardamos fecha
                $fecha = $_POST["fecha"];
                //$fechaFormateada = date('m/Y', strtotime($fecha)); //Convertimos la fecha al formato deseado
                //echo "Fecha de Publicación: <b>" . $fechaFormateada . "</b><br/>";
            } else {
                $errores["fecha"] = "Fecha errónea";
            }

            //Campo Descripcion
            if (
                !empty($_POST["descripcion"])
            ) {
                //Satinizamos
                $descripcion = $_POST["descripcion"];
                $descripcion = trim($descripcion); // Eliminamos espacios en blanco
                $descripcion = htmlspecialchars($descripcion); //Caracteres especiales a HTML
                $descripcion = stripslashes($descripcion); //Elimina barras invertidas
                //echo  "Descripción: <b>" . $descripcion . "</b><br/>";
            } else {
                $errores["descripcion"] = "No puede estar vacío";
            }

            //Campo Imagen
            if (!isset($_FILES["imagen"]) || empty($_FILES["imagen"]["tmp_name"])) {
                $errores["imagen"] = "Seleccione una imagen válida";
            } else {
                /* Realizamos la carga de la imagen en el servidor */
                //       Comprobamos que el campo tmp_name tiene un valor asignado para asegurar que hemos
                //       recibido la imagen correctamente
                //       Definimos la variable $imagen que almacenará el nombre de imagen 
                //       que almacenará la Base de Datos inicializada a NULL
                $imagen = NULL;

                //CAMPO IMAGEN--
                if (isset($_FILES["imagen"]) && (!empty($_FILES["imagen"]["tmp_name"]))) {
                    // Verificamos la carga de la imagen
                    // Comprobamos si existe el directorio fotos, y si no, lo creamos
                    if (!is_dir("fotos")) {
                        $dir = mkdir("fotos", 0777, true);
                    } else {
                        $dir = true;
                    }

                    // Ya verificado que la carpeta uploads existe movemos el fichero seleccionado a dicha carpeta
                    if ($dir) {
                        //Para asegurarnos que el nombre va a ser único...
                        $nombrefichimg = time() . "-" . $_FILES["imagen"]["name"];
                        // Movemos el fichero de la carpeta temportal a la nuestra
                        $movfichimg = move_uploaded_file($_FILES["imagen"]["tmp_name"], "fotos/" . $nombrefichimg);

                        // Definimos el nombre (ruta) de la imagen
                        $imagen = $nombrefichimg;

                        // Verficamos que la carga se ha realizado correctamente
                        if ($movfichimg) {
                            $imagencargada = true;
                            //$imagen = "La imagen nos ha llegado<br/>";
                        } else {
                            $imagencargada = false;
                            $this->mensajes[] = [
                                "tipo" => "danger",
                                "mensaje" => "Error: La imagen no se cargó correctamente! :(",
                            ];
                            $errores["imagen"] = "Error: La imagen no se cargó correctamente! :(";
                        }
                    }
                } else {
                    $errores["imagen"] = "Error en portada, imagen vacía o no recibida";
                }
            } //Fin Imagen

            // Si no se han producido errores realizamos el registro del usuario
            if (count($errores) == 0) {
                echo "no hay errores = vamos a modelo";
                // Guardamos los valores para la ventana modal

                $resultModelo = $this->modelo->agregarentrada([
                    'categoria' => $categoria,
                    "titulo" => $titulo,
                    'descripcion' => $descripcion,
                    'imagen' => $imagen,
                    'fecha' => $fecha
                ]);


                // Si se ha insertado bien en la base de datos
                if ($resultModelo["correcto"]) {
                    $this->mensajes[] = [
                        "tipo" => "success",
                        "mensaje" => "La entrada se registró correctamente!! :)"
                    ];
                } else {
                    $this->mensajes[] = [
                        "tipo" => "danger",
                        "mensaje" => "El usuario no pudo registrarse!! :( <br />({$resultModelo["error"]})"
                    ];
                }
            } else {
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => "Datos de registro de usuario erróneos!! :("
                ];
            }
        } //Fin pulsar guardar
        $parametros = [
            "tituloventana" => "Base de Datos con PHP y PDO",
            "datos" => [
                "categoria" => isset($_POST['categoria']) ? $categoria : "",
                "titulo" => isset($_POST['titulo']) ? $titulo : "",
                "descripcion" => isset($_POST['descripcion']) ? $descripcion : "",
                "fecha" => isset($_POST['fecha']) ? $fecha : "",
                "imagen" => isset($_POST['imagen']) ? $imagen : ""
            ],
            "mensajes" => $this->mensajes
        ];

        //var_dump($parametros);
        //Visualizamos la vista asociada al registro de usuarios
        include_once 'vistas/agregarentrada.php';
    } //Fin Agregarentrada
}
