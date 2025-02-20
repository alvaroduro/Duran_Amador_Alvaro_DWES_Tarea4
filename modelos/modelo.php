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
}
