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
                    $this->listadopag();

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
     * Método del controlador que gestiona la obtención y visualización del listado de entradas.
     * 
     * Se encarga de verificar la sesión del usuario, obtener las entradas desde el modelo
     * y enviar los datos a la vista correspondiente.
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
            $this->registrarOperacion("Listado"); // Registrar la acción en logs
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

    /**
     * Método del controlador para agregar una nueva entrada al sistema.
     * 
     * Se encarga de validar los datos ingresados por el usuario, procesar la imagen
     * y almacenar la información en la base de datos a través del modelo.
     */

    public function agregarentrada()
    {
        // Iniciamos sesión
        $this->iniciarSesion();

        //var_dump($_SESSION['usuario']);
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

    /**
     * Método del controlador para eliminar una entrada del blog.
     *
     * Se encarga de recibir el ID de la entrada desde la vista, 
     * validarlo y solicitar su eliminación en la base de datos a través del modelo.
     */
    public function eliminarentrada()
    {

        // verificamos que hemos recibido los parámetros desde la vista de listado 
        if (isset($_GET['id']) && (is_numeric($_GET['id']))) {
            $id = $_GET["id"];

            //Realizamos la operación de suprimir el usuario con el id=$id
            $resultModelo = $this->modelo->delent($id);

            //Analizamos el valor devuelto por el modelo para definir el mensaje a 
            //mostrar en la vista listado
            if ($resultModelo["correcto"]) :
                $this->registrarOperacion("Eliminar"); // Registrar la acción en logs
                $this->mensajes[] = [
                    "tipo" => "success",
                    "mensaje" => "Se eliminó correctamente la entrada"
                ];
            else :
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => "La eliminación de la Entrada Blog no se realizó correctamente!! :( <br/>({$resultModelo["error"]})"
                ];
            endif;
        } else { //Si no recibimos el valor del parámetro $id generamos el mensaje indicativo:
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "No se pudo acceder al id de la Entrada a eliminar!! :("
            ];
        }
        //Relizamos el listado de los usuarios
        $this->listadopagOrdenado(); // Redirigir al listado
    }

    /**
     * Método del controlador para actualizar los datos de una entrada de blog.
     *
     * Recibe el ID de la entrada desde la vista, valida los datos proporcionados y 
     * actualiza la información en la base de datos.
     */
    public function actentrada()
    {
        // Iniciamos sesión
        $this->iniciarSesion();

        // Array asociativo que almacenará los mensajes de error que se generen por cada campo
        $errores = array();

        // Inicializamos valores de los campos de texto
        $valcategoria = "";
        $valtitulo = "";
        $valfecha = "";
        $valdescripcion = "";
        $valimagen = "";

        // Función que muestra el mensaje de error bajo el campo que no ha superado
        // el proceso de validación
        function mostrar_erroract($errores, $campo)
        {
            $alert = "";
            if (isset($errores[$campo]) && (!empty($campo))) {
                $alert = '<div class="alert alert-danger" style="margin-top:5px;">' . $errores[$campo] . '</div>';
            }
            return $alert;
        }

        // Visualización de las variables obtenidas mediante el formulario en modal
        function valoresfrmact($categoria, $titulo, $fecha, $descripcion)
        {
            echo "<h4>Datos de la actualización entrada <b>" . $titulo . "</b> obtenidos mediante el formulario</h4><br/>";
            echo "<strong>Categoría: </strong>" . $categoria . "<br/>";
            echo "<strong>Titulo: </strong>" . $titulo . "<br/>";
            echo "<strong>Fecha Publicación: </strong>" . $fecha . "<br/>";
            echo "<strong>Descripcion: </strong>" . $descripcion . "<br/>";
            echo "<strong>Imagen: </strong>Imagen recibida<br/>";
        }

        // Si se ha pulsado el botón actualizar...
        if (isset($_POST) && !empty($_POST)) {
            //Realizamos la actualización con los datos existentes en los campos
            $id = $_POST['id']; //Lo recibimos por el campo oculto
            $nuevacategoria = $_POST['categoria'];
            $nuevotitulo  = $_POST['titulo'];
            $nuevadescripcion = $_POST['descripcion'];
            $nuevafecha = $_POST['fecha'];
            $nuevouser = $_POST['iduser'];
            $nuevaimagen = "";

            // Definimos la variable $imagen que almacenará el nombre de imagen 
            // que almacenará la Base de Datos inicializada a NULL
            $imagen = NULL;

            if (isset($_FILES["imagen"]) && (!empty($_FILES["imagen"]["tmp_name"]))) {
                // Verificamos la carga de la imagen
                // Comprobamos si existe el directorio fotos, y si no, lo creamos
                if (!is_dir("fotos")) {
                    $dir = mkdir("fotos", 0777, true);
                } else {
                    $dir = true;
                }
                // Ya verificado que la carpeta fotos existe movemos el fichero seleccionado a dicha carpeta
                if ($dir) {
                    //Para asegurarnos que el nombre va a ser único...
                    $nombrefichimg = time() . "-" . $_FILES["imagen"]["name"];
                    // Movemos el fichero de la carpeta temportal a la nuestra
                    $movfichimg = move_uploaded_file($_FILES["imagen"]["tmp_name"], "fotos/" . $nombrefichimg);
                    $imagen = $nombrefichimg;
                    // Verficamos que la carga se ha realizado correctamente
                    if ($movfichimg) {
                        $imagencargada = true;
                    } else {
                        //Si no pudo moverse a la carpeta destino generamos un mensaje que se le
                        //mostrará al usuario en la vista actuser
                        $imagencargada = false;
                        $errores["imagen"] = "Error: La imagen no se cargó correctamente! :(";
                        $this->mensajes[] = [
                            "tipo" => "danger",
                            "mensaje" => "Error: La imagen no se cargó correctamente! :("
                        ];
                    }
                }
            }
            $nuevaimagen = $imagen;

            //Validamos Campos
            //Campo idCategoria
            if (
                !empty($_POST["categoria"])
                && ($_POST["categoria"] == "accesorios" || $_POST["categoria"] == "consolas")
            ) {

                //Dfinimos el idCategoria
                if ($nuevacategoria == "accesorios") {
                    $nuevacategoria = 1;
                } else {
                    $nuevacategoria = 2;
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
                $nuevotitulo = htmlspecialchars(trim($_POST['titulo']));
                //echo  "Título: <b>" . $titulo . "</b><br/>";
            } else {
                $errores["titulo"] = "No puede estar vacío/No puede contener más de 20 caracteres";
            }

            //Campo Fecha Publicación
            if (!empty($_POST["fecha"])) {
                //Guardamos fecha
                $nuevafecha = $_POST["fecha"];
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
                $nuevadescripcion = $_POST["descripcion"];
                $nuevadescripcion = trim($nuevadescripcion); // Eliminamos espacios en blanco
                $nuevadescripcion = htmlspecialchars($nuevadescripcion); //Caracteres especiales a HTML
                $nuevadescripcion = stripslashes($nuevadescripcion); //Elimina barras invertidas
                //echo  "Descripción: <b>" . $descripcion . "</b><br/>";
            } else {
                $errores["descripcion"] = "No puede estar vacío";
            }

            if (count($errores) == 0) {

                //Ejecutamos la instrucción de actualización a la que le pasamos los valores
                $resultModelo = $this->modelo->actentrada([
                    'id' => $id,
                    'iduser' => $nuevouser,
                    'categoria' => $nuevacategoria,
                    'titulo' => $nuevotitulo,
                    'fecha' => $nuevafecha,
                    'descripcion' => $nuevadescripcion,
                    'imagen' => $nuevaimagen
                ]);
                //Analizamos cómo finalizó la operación de registro y generamos un mensaje
                //indicativo del estado correspondiente
                if ($resultModelo["correcto"]) {
                    $this->mensajes[] = [
                        "tipo" => "success",
                        "mensaje" => "La entrada del blog se actualizó correctamente!! :)"
                    ];
                } else {
                    $this->mensajes[] = [
                        "tipo" => "danger",
                        "mensaje" => "La entrada del blog no pudo actualizarse!! :( <br/>({$resultModelo["error"]})"
                    ];
                }
            } else {
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => "Datos de registro de entrada del blog erróneos!! :("
                ];
            }

            // Obtenemos los valores para mostrarlos en los campos del formulario
            $valcategoria = $nuevacategoria;
            $valtitulo  = $nuevotitulo;
            $valfecha  = $nuevafecha;
            $valdescripcion  = $nuevadescripcion;
            $validuser = $nuevouser;
            $valimagen = $nuevaimagen;

            // Si no se pulsa en actualizar nos traemos los resultados de la consulta
        } else {
            //Estamos rellenando los campos con los valores recibidos del listado
            if (isset($_GET['id']) && (is_numeric($_GET['id']))) {
                $id = $_GET['id'];

                //Ejecutamos la consulta para obtener los datos de la entrada #id
                $resultModelo = $this->modelo->listarentrada($id);

                //Analizamos si la consulta se realiz´correctamente o no y generamos un
                //mensaje indicativo
                if ($resultModelo["correcto"]) {

                    $this->mensajes[] = [
                        "tipo" => "success",
                        "mensaje" => "Los datos de LA ENTRADA blog se obtuvieron correctamente!! :)"
                    ];

                    // Guardamos los valores traidos de la BD
                    $valcategoria = $resultModelo["datos"]["idCategoria"];
                    $valtitulo  = $resultModelo["datos"]["titulo"];
                    $valfecha = $resultModelo["datos"]["fecha"];
                    $valdescripcion = $resultModelo["datos"]["descripcion"];
                    $valimagen = $resultModelo["datos"]["imagen"];
                    $validuser = $resultModelo["datos"]["idUsuario"];
                } else {
                    $this->mensajes[] = [
                        "tipo" => "danger",
                        "mensaje" => "No se pudieron obtener los datos de la Entrada Blog!! :( <br/>({$resultModelo["error"]})"
                    ];
                }
            }
        }

        //Preparamos un array con todos los valores que tendremos que rellenar en
        //la vista adduser: título de la página y campos del formulario
        $parametros = [
            "tituloventana" => "Base de Datos con PHP y PDO",
            "datos" => [
                "categoria" => $valcategoria,
                "ident" => $id,
                "titulo"  => $valtitulo,
                "descripcion"  => $valdescripcion,
                "fecha"  => $valfecha,
                "iduser"  => $validuser,
                "imagen"    => $valimagen
            ],
            "mensajes" => $this->mensajes
        ];
        //Mostramos la vista actuser
        include_once 'vistas/actentrada.php';
    }

    /**
     * Obtiene y muestra el detalle de una entrada específica del blog.
     */
    public function detalleentrada()
    {

        // Iniciamos sesión
        $this->iniciarSesion();

        // verificamos que hemos recibido los parámetros desde la vista de listado 
        if (isset($_GET['id']) && (is_numeric($_GET['id']))) {
            $id = $_GET["id"];

            //Realizamos la operación de suprimir el usuario con el id=$id
            $resultModelo = $this->modelo->detallentrada($id);
            //var_dump($resultModelo);

            //Analizamos el valor devuelto por el modelo para definir el mensaje a 
            //mostrar en la vista listado
            if ($resultModelo["correcto"]) :
                $this->mensajes[] = [
                    "tipo" => "success",
                    "mensaje" => "Se encontró correctamente la entrada"
                ];
            else :
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => "La búsqueda de Entrada Blog no se realizó correctamente!! :( <br/>({$resultModelo["error"]})"
                ];
            endif;
        } else { //Si no recibimos el valor del parámetro $id generamos el mensaje indicativo:
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "No se pudo acceder al id de la Entrada a eliminar!! :("
            ];
        }

        //Preparamos un array con todos los valores que tendremos que rellenar en
        //la vista adduser: título de la página y campos del formulario
        $parametros = [
            "tituloventana" => "Base de Datos con PHP y PDO",
            "datos" => [
                "categoria" => $resultModelo['datos']['nombrecategoria'],
                "titulo" => $resultModelo['datos']['titulo'],
                "nombre"  => $resultModelo['datos']['nombreusuario'],
                "descripcion"  => $resultModelo['datos']['descripcion'],
                "avatar"  => $resultModelo['datos']['avatar'],
                "imagen"    => $resultModelo['datos']['imagen'],
                "fecha"  => $resultModelo['datos']['fecha']
            ],
            "mensajes" => $this->mensajes
        ];
        //Mostramos la vista detalle
        include_once 'vistas/detalle.php';
    }

    /**
     * Listado por paginacion,  muestra los resultados en un 
     * array llamado parametros con los datos, mensajes, pagina inicial
     * y el total de resultaos de la consulta en modelo
     * 
     */
    public function listadopag()
    {

        // Iniciamos sesión
        $this->iniciarSesion();

        // Parámetros para la vista
        $parametros = [
            "tituloventana" => "Listado Entradas",
            "datos" => NULL,
            "mensajes" => [],
            "pagina" => 1, // Página por defecto
            "totalresultados" => 0 //Empezamos por 0
        ];

        // Comprobamos si hay un parámetro 'pagina' en la URL
        if (isset($_GET['pagina'])) {
            $parametros['pagina'] = (int)$_GET['pagina'];
        }

        //var_dump($parametros['pagina']);
        // Número de resultados por página
        $resultados_por_pagina = 8;

        //var_dump($_SESSION['usuario']);
        // Total resultados de registros
        if ($_SESSION['usuario']['rol'] == "user") {
            //echo "controlado user";
            $parametros['totalresultados'] = $this->modelo->totalEntradasPorUsuario($_SESSION['usuario']['iduser']);
            // var_dump($parametros['totalresultados']);
        } else if ($_SESSION['usuario']['rol'] == "admin") {
            //echo "controlado admin";
            $parametros['totalresultados'] = $this->modelo->totalEntradasGenerales();
        }

        // Obtenemos los resultados desde el modelo
        $resultsModelo = $this->modelo->listadopag($_SESSION['usuario']['iduser'], $parametros['pagina'], $resultados_por_pagina);

        if ($resultsModelo["correcto"]) {
            $parametros['datos'] = $resultsModelo['datos'];
            $this->registrarOperacion("Listado"); // Registrar la acción en logs
            $this->mensajes[] = [
                "tipo" => "success",
                "mensaje" => "El listado se realizó correctamente"
            ];
        } else {
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "El listado no pudo realizarse correctamente!! <br/>({$resultsModelo['error']})"
            ];
            $parametros['mensajes'] = $this->mensajes;
            include_once 'vistas/login.php';
            return;
        }

        // Asignamos los mensajes al array de parámetros
        $parametros['mensajes'] = $this->mensajes;

        // Incluimos la vista
        include_once 'vistas/listado.php';
    }

    /**
     * Genera y descarga un archivo PDF con el listado de entradas.
     */
    public function pdf()
    {

        // Limpia cualquier salida anterior para evitar errores en la generación del PDF
        ob_clean(); // Limpia cualquier salida anterior al inicio

        require('fpdf/fpdf.php'); // Incluye la biblioteca FPDF

        // Iniciamos sesión
        $this->iniciarSesion();
        $this->compruebasesion(); //Comprobamos si hay sesion de usuario iniciada

        // Vemos si es user o admin
        //Realizamos la consulta y almacenamos en nuestra variable
        $resultsModelo = $this->modelo->listado($_SESSION['usuario']['iduser']);

        // Verificamos si la consulta fue exitosa
        if ($resultsModelo['correcto']) {
            // Creamos un nuevo objeto FPDF
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 12);

            // Encabezado
            $pdf->Cell(0, 10, 'Listado de Entradas', 0, 1, 'C');
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Ln(10);
            $pdf->Cell(20, 10, 'Categoria', 1, 0, 'C');
            $pdf->Cell(40, 10, 'Titulo', 1, 0, 'C');
            if ($_SESSION['usuario']['rol'] == 'admin') {
                $pdf->Cell(35, 10, 'Email', 1, 0, 'C');
            }

            $pdf->Cell(25, 10, 'Fecha', 1, 0, 'C');
            $pdf->Cell(65, 10, 'Descripcion', 1, 1, 'C');

            // Llenamos las filas con los datos
            $pdf->SetFont('Arial', '', 6);

            //var_dump($resultsModelo['datos']);
            foreach ($resultsModelo['datos'] as $e) {
                // Decodifica las entidades HTML de la descripción y elimina etiquetas HTML
                $descripcionN = html_entity_decode($e["descripcion"]);
                $pdf->Cell(20, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $e['categoria']), 1, 0, 'C');

                // Si el usuario es administrador, se agrega la columna de email
                $pdf->Cell(40, 10, iconv('UTF-8', 'ISO-8859-1', $e['titulo']), 1, 0, 'C');
                if ($_SESSION['usuario']['rol'] == 'admin') {
                    $pdf->Cell(35, 10, iconv('UTF-8', 'ISO-8859-1', $e['email']), 1, 0, 'C');
                }

                $pdf->Cell(25, 10, date('d/m/Y', strtotime($e['fecha'])), 1, 0, 'C');
                $pdf->MultiCell(65, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', strip_tags($descripcionN)), 1, 'C');

                // Agrega un salto de línea para la siguiente fila
                $pdf->Ln();
            }
        } else {
            // Si hay un error en la consulta, muestra un mensaje en el PDF
            $pdf->Cell(0, 10, 'Error al obtener datos: ' . $e->getMessage(), 0, 1);
        }
        // Enviar el archivo PDF al navegador
        ob_end_clean(); // Limpia el buffer de salida
        $pdf->Output('D', 'listado_entradas.pdf');
    }

    /**
     * Función para mostrar un listado de registro entradas ordenados
     * segun decida el usuario con un boton para acendente o descendente 
     */
    public function listadopagOrdenado()
    {
        // Iniciamos sesión
        $this->iniciarSesion();

        // Verificamos si hay un parámetro 'orden' en la URL
        if (isset($_GET['orden']) && ($_GET['orden'] === 'ASC' || $_GET['orden'] === 'DESC')) {
            $orden = $_GET['orden'];
        } else {
            $orden = 'DESC'; //Si no, ponemos por defecto uno
        }

        // Parámetros para la vista
        $parametros = [
            "tituloventana" => "Listado Entradas",
            "datos" => NULL,
            "mensajes" => [],
            "pagina" => 1, // Página por defecto
            "totalresultados" => 0, // Empezamos por 0
            "orden" => $orden // Orden 
        ];

        // Verificamos si hay un parámetro 'pagina' en la URL
        if (isset($_GET['pagina']) && is_numeric($_GET['pagina'])) {
            $parametros['pagina'] = (int)$_GET['pagina'];
        }

        // Número de resultados por página
        $resultados_por_pagina = 8;

        // Verificamos el rol del usuario para obtener el total de entradas
        if ($_SESSION['usuario']['rol'] == "user") {
            $parametros['totalresultados'] = $this->modelo->totalEntradasPorUsuario($_SESSION['usuario']['iduser']);
        } else if ($_SESSION['usuario']['rol'] == "admin") {

            $parametros['totalresultados'] = $this->modelo->totalEntradasGenerales();
            //var_dump($_SESSION['usuario']);
            ///var_dump($parametros);
        }

        // Obtenemos los resultados desde el modelo, ahora con orden dinámico
        $resultsModelo = $this->modelo->listadopagOrdenado($_SESSION['usuario']['iduser'], $parametros['pagina'], $resultados_por_pagina, $parametros['orden']);

        if ($resultsModelo["correcto"]) {
            $parametros['datos'] = $resultsModelo['datos'];
            $this->registrarOperacion("Listado"); // Registrar la acción en logs
            $this->mensajes[] = [
                "tipo" => "success",
                "mensaje" => "El listado se realizó correctamente"
            ];
        } else {
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "El listado ordenado no pudo realizarse correctamente!! <br/>({$resultsModelo['error']})"
            ];
            $parametros['mensajes'] = $this->mensajes;
            include_once 'vistas/login.php';
            return;
        }

        // Asignamos los mensajes al array de parámetros
        $parametros['mensajes'] = $this->mensajes;

        // Incluimos la vista
        include_once 'vistas/listado.php';
    }

    /**
     * Función para crear la tabla de logs si no existe en la base de datos.
     *
     * Esta función verifica si la tabla 'logs' ya existe en la base de datos, 
     * y si no es así, la crea. En caso de que la tabla ya exista, se muestra 
     * un mensaje informativo. Los mensajes de éxito o error son almacenados 
     * en el array de mensajes para ser mostrados en la vista.
     */
    public function crearTablaLogs()
    {
        try {

            // Iniciamos sesión
            $this->iniciarSesion();
            // Verificar si la tabla 'logs' ya existe
            $sql_check = "SHOW TABLES LIKE 'logs'";
            $query = $this->modelo->ejecutarSQL($sql_check);
            var_dump($query["datos"]);
            //var_dump(count($query["datos"]));

            if ($query["datos"]) {

                // La tabla ya existe, mensaje informativo
                $this->mensajes[] = [
                    "tipo" => "info",
                    "mensaje" => "La tabla 'logs' ya existe en la base de datos."
                ];
            } else {

                // Si la tabla no existe la creamos
                // Conectamos con la base de datos
                $sql = "CREATE TABLE IF NOT EXISTS logs (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        fecha DATE NOT NULL,
                        hora TIME NOT NULL,
                        usuario VARCHAR(100) NOT NULL,
                        operacion VARCHAR(50) NOT NULL
                    ) ENGINE=InnoDB";

                // Ejecutamos la consulta
                // Ejecutamos la consulta para crear la tabla
                $resultado = $this->modelo->ejecutarSQL($sql);

                // Guardamos los mensjaes posibles

                //En caso de no ser correcto la creación de la tabla
                if (!$resultado['correcto']) {
                    $this->mensajes[] = [
                        "tipo" => "danger",
                        "mensaje" => "Error al crear la tabla 'logs': " . $resultado['error']
                    ];
                } else {
                    $this->mensajes[] = [
                        "tipo" => "success",
                        "mensaje" => "La tabla 'logs' ha sido creada correctamente."
                    ];
                }
            }
        } catch (PDOException $ex) {
            // Mensaje de error
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "Error al crear la tabla 'logs': " . $ex->getMessage()
            ];
        }

        // Asignamos los mensajes al array de parámetros
        $parametros['mensajes'] = $this->mensajes;

        // Redirigir a tablalog.php
        include 'vistas/tablalog.php';
    }


    /**
     * Registra una operación en la tabla de logs.
     * Este método es llamado para registrar automáticamente una operación.
     *
     * @param string $operacion La operación que se va a registrar.
     */
    public function registrarOperacion($operacion)
    {
        $this->iniciarSesion();
        // Verificar si el usuario está autenticado
        if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario']['email'])) {
            $usuario = $_SESSION['usuario']['email']; // Obtener email del usuario
        } else {
            $usuario = "Desconocido"; // En caso de error
        }

        // Llamamos al modelo para registrar la operación
        $this->modelo->registrarLog($usuario, $operacion);
    }

    /**
     * Función para obtener y mostrar un listado paginado de los logs.
     */
    public function listadopaglog()
    {

        // Iniciamos sesión
        $this->iniciarSesion();

        // Parámetros para la vista
        $parametros = [
            "tituloventana" => "Listado Entradas",
            "datos" => NULL,
            "mensajes" => [],
            "pagina" => 1, // Página por defecto
            "totalresultados" => 0 //Empezamos por 0
        ];

        // Comprobamos si hay un parámetro 'pagina' en la URL
        if (isset($_GET['pagina'])) {
            $parametros['pagina'] = (int)$_GET['pagina'];
        }

        //var_dump($parametros['pagina']);
        // Número de resultados por página
        $resultados_por_pagina = 4;

        $parametros['totalresultados'] = $this->modelo->totalEntradasLog();

        // Obtenemos los resultados desde el modelo
        $resultsModelo = $this->modelo->listadopaglog($parametros['pagina'], $resultados_por_pagina);

        if ($resultsModelo["correcto"]) {
            $parametros['datos'] = $resultsModelo['datos'];
            $this->mensajes[] = [
                "tipo" => "success",
                "mensaje" => "El listado logs se realizó correctamente"
            ];
        } else {
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "El listado logs no pudo realizarse correctamente!! <br/>({$resultsModelo['error']})"
            ];
            $parametros['mensajes'] = $this->mensajes;
        }

        // Asignamos los mensajes al array de parámetros
        $parametros['mensajes'] = $this->mensajes;

        // Incluimos la vista
        include_once 'vistas/listadologs.php';
    }

    /**
     * Elimina un registro de log por su ID.
     *
     * @param int $id El ID del registro de log que se desea eliminar.
     */
    public function eliminarentradalog()
    {
        $this->iniciarSesion();

        // verificamos que hemos recibido los parámetros desde la vista de listado 
        if (isset($_GET['id']) && (is_numeric($_GET['id']))) {
            $id = $_GET["id"];

            //Realizamos la operación de suprimir el usuario con el id=$id
            $resultModelo = $this->modelo->delentlog($id);

            //Analizamos el valor devuelto por el modelo para definir el mensaje a 
            //mostrar en la vista listado
            if ($resultModelo["correcto"]) :
                $this->registrarOperacion("Eliminar"); // Registrar la acción en logs
                $this->mensajes[] = [
                    "tipo" => "success",
                    "mensaje" => "Se eliminó correctamente el registro de log"
                ];
            else :
                $this->mensajes[] = [
                    "tipo" => "danger",
                    "mensaje" => "La eliminación del registro logs no se realizó correctamente!! :( <br/>({$resultModelo["error"]})"
                ];
            endif;
        } else { //Si no recibimos el valor del parámetro $id generamos el mensaje indicativo:
            $this->mensajes[] = [
                "tipo" => "danger",
                "mensaje" => "No se pudo acceder al id de la Entrada a eliminar!! :("
            ];
        }
        //Relizamos el listado de los usuarios
        $this->listadopaglog(); // Redirigir al listado
    }

    /**
     * Función para generar un archivo PDF con el listado de logs.
     */
    public function pdflog()
    {

        // Limpia cualquier salida anterior para evitar errores en la generación del PDF
        ob_clean(); // Limpia cualquier salida anterior al inicio

        require('fpdf/fpdf.php'); // Incluye la biblioteca FPDF

        // Iniciamos sesión
        $this->iniciarSesion();
        $this->compruebasesion(); //Comprobamos si hay sesion de usuario iniciada

        // Vemos si es user o admin
        //Realizamos la consulta y almacenamos en nuestra variable
        $resultsModelo = $this->modelo->listadolog();

        // Verificamos si la consulta fue exitosa
        if ($resultsModelo['correcto']) {
            // Creamos un nuevo objeto FPDF
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 12);

            // Encabezado
            $pdf->Cell(0, 10, 'Listado de Logs', 0, 1, 'C');
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Ln(10);
            $pdf->Cell(20, 10, 'Id', 1, 0, 'C');
            $pdf->Cell(40, 10, 'fecha', 1, 0, 'C');
            $pdf->Cell(40, 10, 'Hora', 1, 0, 'C');
            $pdf->Cell(35, 10, 'Usuario', 1, 0, 'C');
            $pdf->Cell(25, 10, 'Operacion', 1, 0, 'C');

            // Llenamos las filas con los datos
            $pdf->SetFont('Arial', '', 6);

            //var_dump($resultsModelo['datos']);
            foreach ($resultsModelo['datos'] as $e) {
                $pdf->Cell(20, 10, $e['id'], 1, 0, 'C');
                $pdf->Cell(40, 10, date('d/m/Y', strtotime($e['fecha'])), 1, 0, 'C');
                $pdf->Cell(40, 10, date('H:i:s', strtotime($e['hora'])), 1, 0, 'C');
                $pdf->Cell(35, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $e['usuario']), 1, 0, 'C');
                $pdf->Cell(25, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $e['operacion']), 1, 0, 'C');

                // Agrega un salto de línea para la siguiente fila
                $pdf->Ln();
            }
        } else {
            // Si hay un error en la consulta, muestra un mensaje en el PDF
            $pdf->Cell(0, 10, 'Error al obtener datos: ' . $e->getMessage(), 0, 1);
        }
        // Enviar el archivo PDF al navegador
        ob_end_clean(); // Limpia el buffer de salida
        $pdf->Output('D', 'listado_logs.pdf');
    }
}
