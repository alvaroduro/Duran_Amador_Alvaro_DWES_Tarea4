<?php

/** Clase 'modelo' que implementa el modelo de nuestra app en una arquitectura MVC.
 * se encarga de gestionar el acceso a la BD en una capa especializada
 */

class modelo
{
    private $conexion; //Att que contendra la referencia a la BD

    //Parametro para la conexion a la BD
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $bd = "bdblog";

    //Constructor de la clase que ejecuta el método 'conectar()'
    public function __construct()
    {
        $this->conectar();
    }

    /** Metodo que realiza la conexion a la BD de usuarios medinte PDO. Devuelve un 
     * TRUE si la conexion se realizo correctamente o el mensaje generado por la excepcion si hay
     * aalgun error
     */
    /** El metodo conectar visto anteriormente, llamara al constructor para crear un objeto modelo
     * Iguala a lo que anteriormente seria el config.php
     */
    public function conectar()
    {
        //Metemos en un bloque try-catch
        try {
            $this->conexion =
                //Definimos los parametros
                new PDO("mysql:host=$this->host;dbname=$this->bd", $this->user, $this->pass);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $return = true;
        } catch (Exception $ex) {
            //Devolvemos mesnaje error
            $return = $ex->getMessage();
        }

        //Devolvemos true o false
        return $return;
    }

    /**
     * Función que nos permite conocer si estamos conectados o no a la base de datos.
     * Devuelve TRUE si se realizó correctamente y FALSE en caso contrario.
     * @return boolean
     */
    public function estaConectado()
    {
        if ($this->conexion) :
            return TRUE;
        else :
            return FALSE;
        endif;
    }

    /**
     * Funcion para traer un usuario de la base de datos y con el id
     * Devuelve un array con los resultados obtenidos
     * @return array (correcto,datos,error)
     */
    public function listausuario($id)
    {
        $return = [
            "correcto" => FALSE,
            "datos" => NULL,
            "error" => NULL
        ];

        if ($id && is_numeric($id)) {
            try {
                $sql = "SELECT * FROM usuarios WHERE id=:id";
                $query = $this->conexion->prepare($sql);
                $query->execute(['id' => $id]);
                //Supervisamos que la consulta se realizó correctamente... 
                if ($query) {
                    $return["correcto"] = TRUE;
                    $return["datos"] = $query->fetch(PDO::FETCH_ASSOC);

                    //Si no devuelve ningun dato
                    if (empty($return["datos"])) {
                        $return["correcto"] = FALSE;
                        $return["error"] = "No existe ningún usuario con ese Email";
                    }
                } // o no :(
            } catch (PDOException $ex) {
                $return["error"] = $ex->getMessage();
                //die();
            }
        }

        return $return;
    }

    /**
     * Funcion que al pasarle un email y un password busca en la Base de Datos
     * y nos devuelve si existen los campos o si no 
     * @return array (correcto,datos,error)
     */
    public function login($email, $password)
    {
        $return = [
            "correcto" => FALSE,
            "datos" => NULL,
            "error" => NULL
        ];

        // Si hay un email lo filtramos y validamos
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $sql = "SELECT * FROM usuarios WHERE email = :email";
                $query = $this->conexion->prepare($sql);
                $query->execute(['email' => $email]);

                // Si encuentra algun resultado
                if ($query->rowCount() > 0) {

                    // Guardamos el resultado en una variable
                    $usuario = $query->fetch(PDO::FETCH_ASSOC);

                    // Iniciamos sesion 
                    session_start();
                    $_SESSION['usuario'] = $usuario; //Guardamos la sesion del usuario

                    // Verificamos la contraseña (asumiendo que está hasheada con password_hash())
                    if (password_verify($password, password_hash($_SESSION['usuario']['contrasenia'], PASSWORD_DEFAULT))) {

                        // Almacenamos en nuestra variable los resultados
                        $return["correcto"] = TRUE;
                        $return["datos"] = $usuario;
                        var_dump($return);
                    }
                }
            } catch (PDOException $ex) {
                $return["error"] = $ex->getMessage();
            }
        }

        return $return;
    }

    /**
     * Funcion para buscar datos en la base de datos de las diferentes tablas
     * segun la consulta que manejamos y la almacenamos en un array junto con
     * @return type datos, o mensaje de error base de datos
     */
    public function listado($id)
    {
        //Variable para devolver los resultados
        $return = ["correcto" => FALSE, "datos" => NULL, "error" => NULL];

        //Realizamos la consulta
        try {
            $sql = "SELECT * FROM entradas e 
        INNER JOIN usuarios u ON e.idUsuario = u.iduser
        WHERE u.iduser = :id";

            $resultquery = $this->conexion->prepare($sql);
            $resultquery->execute(['id' => $id]);

            //Supervisamos si todo ha ido bien
            if ($resultquery) {
                //Si la consulta ha ido bien
                $return['correcto'] = TRUE;
                //Almacenamos todos los datos del usuario
                $return['datos'] = $resultquery->fetchAll(PDO::FETCH_ASSOC);
                var_dump($return);
            }
        } catch (Exception $ex) {
            $return['error'] = $ex->getMessage();
        }
        //Devolvemos la variable con sus parametros
        return $return;
    }
}
