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
                //$recordar = isset($_POST['recordar']); //Pulsado btn recordar user
                //$mantenerSesion = isset($_POST['mantenerSesion']); //sesion abierta

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
        //Almacenamos en el array los valores mostrados en la vista
        $parametros = [
            "tituloventana" => "MVC Blog con PHP y PDO",
            "datos" => NULL,
            "mensajes" => []
        ];

        //var_dump($_SESSION['usuario']['iduser']);
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
        session_start(); // Asegura que la sesión está iniciada

        // Si hay cookies de sesión activas, las eliminamos
        if (isset($_COOKIE['usuario'])) {
            setcookie('usuario', '', time() - 3600, '/'); // Expira la cookie
        }

        // Destruir la sesión
        session_destroy();

        // Redirigir al login
        //incluimos la vista a mostrar
        include_once 'vistas/login.php';
        exit();
    }
}
