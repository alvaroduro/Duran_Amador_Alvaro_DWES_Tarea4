<!--Clase para controlar la logica del programa y enlazar VC-->
<?php
require_once 'modelos/modelo.php'; // Incluir la clase modelo antes de usarla
class controlador
{

    //Definimos el att modelo de la 'clase modelo' y ser a traves el cual
    //Podremos acceder a los datos y las operaciones de la BD desde el controlador
    private $modelo;

    //$mensajes se utiliza para almacenr los sms generados en las tareas,
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
        $parametros = ["tituloventana" => "Base de Datos con PHP y PDO"];

        //Mostramos la pag inicio 
        //include_once 'vistas/inicio.php';
    }
}
