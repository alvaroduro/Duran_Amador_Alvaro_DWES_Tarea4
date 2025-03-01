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

                    // Guardamos la sesion del usuario
                    $_SESSION['usuario'] = $usuario; //Guardamos la sesion del usuario

                    // Verificamos la contraseña (asumiendo que está hasheada con password_hash())
                    if (password_verify($password, password_hash($_SESSION['usuario']['contrasenia'], PASSWORD_DEFAULT))) {

                        // Almacenamos en nuestra variable los resultados
                        $return["correcto"] = TRUE;
                        $return["datos"] = $usuario;
                        //var_dump($return);
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
        $id = $_SESSION['usuario']['iduser'];
        //Realizamos la consulta
        try {

            // Si es USUARIO traemos sus resultados
            if ($_SESSION['usuario']['rol'] == 'user') {
                $sql = "SELECT 
                        c.nombrecat AS categoria, 
                        e.ident,
                        e.titulo, 
                        e.imagen, 
                        e.descripcion, 
                        e.fecha, 
                        u.email, 
                        u.nombre, 
                        u.avatar, 
                        u.iduser
                    FROM entradas e
                    JOIN usuarios u ON e.idUsuario = u.iduser
                    JOIN categoria c ON e.idCategoria = c.idcat
                    WHERE u.iduser = :id
                    ORDER BY e.fecha DESC";

                $resultquery = $this->conexion->prepare($sql);
                $resultquery->execute(['id' => $id]);

                // Si es ADMIN traemos todas las entradas
            } elseif ($_SESSION['usuario']['rol'] == 'admin') {
                $sql = "SELECT 
                        c.nombrecat AS categoria, 
                        e.*,
                        u.email, 
                        u.nombre, 
                        u.avatar, 
                        u.iduser
                    FROM entradas e
                    JOIN usuarios u ON e.idUsuario = u.iduser
                    JOIN categoria c ON e.idCategoria = c.idcat
                    ORDER BY e.fecha DESC";

                $resultquery = $this->conexion->prepare($sql);
                $resultquery->execute();
            }

            //Supervisamos si todo ha ido bien
            if ($resultquery) {
                //Si la consulta ha ido bien
                $return['correcto'] = TRUE;
                //Almacenamos todos los datos del usuario
                $return['datos'] = $resultquery->fetchAll(PDO::FETCH_ASSOC);
                //var_dump($return);
            }
        } catch (Exception $ex) {
            $return['error'] = $ex->getMessage();
        }
        //Devolvemos la variable con sus parametros
        return $return;
    }

    /**
     * Método para obtener los datos de una entrada específica en la base de datos.
     *
     * @param int $id Identificador único de la entrada a consultar.
     *
     * @return array Retorna un array con los siguientes valores:
     *               - 'correcto' (bool): Indica si la consulta se realizó correctamente.
     *               - 'datos' (array|null): Contiene los datos de la entrada si la consulta fue exitosa, NULL en caso contrario.
     *               - 'error' (string|null): Contiene el mensaje de error en caso de fallo.
     */
    public function listarentrada($id)
    {
        $return = [
            "correcto" => FALSE,
            "datos" => NULL,
            "error" => NULL
        ];

        if ($id && is_numeric($id)) {
            try {
                $sql = "SELECT * FROM entradas WHERE ident=:id";
                $query = $this->conexion->prepare($sql);
                $query->execute(['id' => $id]);
                //Supervisamos que la consulta se realizó correctamente... 
                if ($query) {
                    $return["correcto"] = TRUE;
                    $return["datos"] = $query->fetch(PDO::FETCH_ASSOC);
                } // o no :(
            } catch (PDOException $ex) {
                $return["error"] = $ex->getMessage();
                //die();
            }
        }

        return $return;
    }


    /**
     * Funcion mediante la cual a traves de 3 parámetros:
     * Campo de la tabl, tabla y dato a validar, nos devuelve si existe o no en la 
     * tabla
     * @return array si es correcto o no(existe o no en la BD)
     */
    public function verificarCampo($campo, $tabla, $dato)
    {
        try {
            // Consulta para verificar si el campo existe en la tabla especificada
            $sql = "SELECT COUNT(*) AS total FROM $tabla WHERE $campo = :dato";
            $query = $this->conexion->prepare($sql);
            $query->execute(['dato' => $dato]);

            // Obtenemos el resultado de la consulta
            $resultado = $query->fetch(PDO::FETCH_ASSOC);

            // Si el resultado es mayor que 0, el campo existe
            if ($resultado['total'] > 0) {
                $return = FALSE;
            } else {
                $return = TRUE;
            };
        } catch (PDOException $ex) {
            // Manejo del error - mostramos el posible error
            $return = $ex->getMessage();
        }

        return $return;
    }

    /**
     * Agrega una nueva entrada en la base de datos.
     *
     * @param array $datos Datos de la entrada a agregar (categoría, título, imagen, descripción, fecha).
     * @return array Retorna un array con:
     *               - "correcto" (bool): TRUE si la inserción fue exitosa, FALSE si hubo un error.
     *               - "error" (string|null): Mensaje de error en caso de fallo.
     */
    public function agregarentrada($datos)
    {
        $return = [
            "correcto" => FALSE,
            "error" => NULL
        ];

        try {
            //Inicializamos la transacción
            $this->conexion->beginTransaction();

            //Definimos la instrucción SQL parametrizada 
            $sql = "INSERT INTO entradas(idusuario,idcategoria,titulo,imagen,descripcion,fecha)
                         VALUES (:idusuario,:idcategoria,:titulo,:imagen,:descripcion,:fecha)";

            // Preparamos la consulta...
            $query = $this->conexion->prepare($sql);

            // y la ejecutamos indicando los valores que tendría cada parámetro
            $query->execute([
                'idusuario' => $_SESSION['usuario']['iduser'],
                'idcategoria' => $datos["categoria"],
                'titulo' => $datos["titulo"],
                'imagen' => $datos["imagen"],
                'descripcion' => $datos["descripcion"],
                'fecha' => $datos["fecha"]
            ]);

            //Supervisamos si la inserción se realizó correctamente... 
            if ($query) {
                $this->conexion->commit(); // commit() confirma los cambios realizados durante la transacción
                $return["correcto"] = TRUE;
            }
            // o no :(
        } catch (PDOException $ex) {
            $this->conexion->rollback(); // rollback() se revierten los cambios realizados durante la transacción
            $return["error"] = $ex->getMessage();
            //die();
        }

        return $return;
    }

    /**
     * Elimina una entrada de la base de datos según su ID.
     *
     * @param int $id ID de la entrada a eliminar.
     * @return array Retorna un array con:
     *               - "correcto" (bool): TRUE si la eliminación fue exitosa, FALSE si hubo un error.
     *               - "error" (string|null): Mensaje de error en caso de fallo.
     */
    public function delent($id)
    {
        // La función devuelve un array con dos valores:'correcto', que indica si la
        // operación se realizó correctamente, y 'mensaje', campo a través del cual le
        // mandamos a la vista el mensaje indicativo del resultado de la operación
        $return = [
            "correcto" => FALSE,
            "error" => NULL
        ];

        //Si hemos recibido el id y es un número realizamos el borrado...
        if ($id && is_numeric($id)) {
            try {
                //Inicializamos la transacción
                $this->conexion->beginTransaction();

                //Definimos la instrucción SQL parametrizada 
                $sql = "DELETE FROM entradas WHERE ident=:id";
                $query = $this->conexion->prepare($sql);
                $query->execute(['id' => $id]);

                //Supervisamos si la eliminación se realizó correctamente... 
                if ($query) {
                    $this->conexion->commit();  // commit() confirma los cambios realizados durante la transacción
                    $return["correcto"] = TRUE;
                } // o no :(
            } catch (PDOException $ex) {
                $this->conexion->rollback(); // rollback() se revierten los cambios realizados durante la transacción
                $return["error"] = $ex->getMessage();
            }
        } else {
            $return["correcto"] = FALSE;
        }

        return $return;
    }

    /**
     * Método para actualizar una entrada en la base de datos.
     *
     * @param array $datos Datos de la entrada a actualizar
     *
     * @return array Retorna un array con:
     *               - 'correcto' (bool): Indica si la actualización fue exitosa.
     *               - 'error' (string|null): Mensaje de error en caso de fallo.
     */
    public function actentrada($datos)
    {
        $return = [
            "correcto" => FALSE,
            "error" => NULL
        ];

        try {
            //Inicializamos la transacción
            $this->conexion->beginTransaction();
            //Definimos la instrucción SQL parametrizada 
            $sql = "UPDATE entradas SET idUsuario= :idUsuario, idCategoria= :idCategoria, titulo= :titulo, imagen= :imagen, fecha= :fecha, descripcion= :descripcion WHERE ident=:id";
            $query = $this->conexion->prepare($sql);
            $query->execute([
                'id' => $datos["id"],
                'idUsuario' => $datos["iduser"],
                'idCategoria' => $datos["categoria"],
                'titulo' => $datos["titulo"],
                'fecha' => $datos["fecha"],
                'descripcion' => $datos["descripcion"],
                'imagen' => $datos["imagen"]
            ]);
            //Supervisamos si la inserción se realizó correctamente... 
            if ($query) {
                $this->conexion->commit();  // commit() confirma los cambios realizados durante la transacción
                $return["correcto"] = TRUE;
            } // o no :(
        } catch (PDOException $ex) {
            $this->conexion->rollback(); // rollback() se revierten los cambios realizados durante la transacción
            $return["error"] = $ex->getMessage();
            //die();
        }

        return $return;
    }

    public function detallentrada($id)
    {
        // La función devuelve un array con dos valores:'correcto', que indica si la
        // operación se realizó correctamente, y 'mensaje', campo a través del cual le
        // mandamos a la vista el mensaje indicativo del resultado de la operación
        $return = [
            "correcto" => FALSE,
            "error" => NULL
        ];

        //Si hemos recibido el id y es un número realizamos el borrado...
        if ($id && is_numeric($id)) {
            try {
                //Inicializamos la transacción
                $this->conexion->beginTransaction();

                //Definimos la instrucción SQL parametrizada 
                $sql = "SELECT 
                e.ident, e.titulo, e.imagen, e.descripcion, e.fecha,
                c.nombrecat AS nombrecategoria,
                u.nombre AS nombreusuario, u.avatar
                    FROM entradas e
                    JOIN categoria c ON e.idCategoria = c.idcat
                    JOIN usuarios u ON e.idUsuario = u.iduser
                    WHERE e.ident = :id";
                $query = $this->conexion->prepare($sql);
                $query->execute(['id' => $id]);

                //Supervisamos si la consulta se realizó correctamente... 
                if ($query) {
                    $this->conexion->commit();  // commit() confirma los cambios realizados durante la transacción
                    $return["correcto"] = TRUE;
                    $return["datos"] = $query->fetch(PDO::FETCH_ASSOC);
                } // o no :(
            } catch (PDOException $ex) {
                $this->conexion->rollback(); // rollback() se revierten los cambios realizados durante la transacción
                $return["error"] = $ex->getMessage();
            }
        } else {
            $return["correcto"] = FALSE;
        }

        return $return;
    }

    /**
     * Listado por paginacion
     * 
     */
    public function listadopag($id, $pagina = 1, $resultados_por_pagina = 8)
    {
        // Variable para devolver los resultados
        $return = ["correcto" => FALSE, "datos" => NULL, "error" => NULL];

        // Calcular el OFFSET
        $offset = ($pagina - 1) * $resultados_por_pagina;

        try {
            // Si es USUARIO, traemos sus resultados
            if ($_SESSION['usuario']['rol'] == 'user') {
                $sql = "SELECT 
                        c.nombrecat AS categoria, 
                        e.ident,
                        e.titulo, 
                        e.imagen, 
                        e.descripcion, 
                        e.fecha, 
                        u.email, 
                        u.nombre, 
                        u.avatar, 
                        u.iduser
                    FROM entradas e
                    JOIN usuarios u ON e.idUsuario = u.iduser
                    JOIN categoria c ON e.idCategoria = c.idcat
                    WHERE u.iduser = :id
                    ORDER BY e.fecha DESC
                    LIMIT :limit OFFSET :offset";

                $resultquery = $this->conexion->prepare($sql);
                $resultquery->bindParam(':id', $id, PDO::PARAM_INT);
                $resultquery->bindParam(':limit', $resultados_por_pagina, PDO::PARAM_INT);
                $resultquery->bindParam(':offset', $offset, PDO::PARAM_INT);
                $resultquery->execute();
            } elseif ($_SESSION['usuario']['rol'] == 'admin') {
                $sql = "SELECT 
                        c.nombrecat AS categoria, 
                        e.*,
                        u.email, 
                        u.nombre, 
                        u.avatar, 
                        u.iduser
                    FROM entradas e
                    JOIN usuarios u ON e.idUsuario = u.iduser
                    JOIN categoria c ON e.idCategoria = c.idcat
                    ORDER BY e.fecha DESC
                    LIMIT :limit OFFSET :offset";

                $resultquery = $this->conexion->prepare($sql);
                $resultquery->bindParam(':limit', $resultados_por_pagina, PDO::PARAM_INT);
                $resultquery->bindParam(':offset', $offset, PDO::PARAM_INT);
                $resultquery->execute();
            }

            // Supervisamos si todo ha ido bien
            if ($resultquery) {
                $return['correcto'] = TRUE;
                $return['datos'] = $resultquery->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $ex) {
            $return['error'] = $ex->getMessage();
        }

        return $return;
    }

    /** Función para obtener el total de entradas de un usuario específico */
    public function totalEntradasPorUsuario($id)
    {

        $return = [
            "correcto" => FALSE,
            "datos" => NULL,
            "error" => NULL
        ];

        // Verificamos que el id sea válido
        if ($id && is_numeric($id)) {
            try {
                $sql = "SELECT COUNT(*) AS total FROM entradas WHERE idUsuario = :id";
                $query = $this->conexion->prepare($sql);
                $query->execute(['id' => $id]);

                // Verificamos si la consulta fue exitosa
                if ($query) {
                    $result = $query->fetch(PDO::FETCH_ASSOC);
                    $return["correcto"] = TRUE;
                    $return["datos"] = $result["total"];  // El total de entradas del usuario
                }
            } catch (PDOException $ex) {
                $return["error"] = $ex->getMessage();
            }
        }

        return $return;
    }

    /** Función para obtener el total de todas las entradas sin filtrar por usuario */
    public function totalEntradasGenerales()
    {
        $return = [
            "correcto" => FALSE,
            "datos" => NULL,
            "error" => NULL
        ];

        try {
            $sql = "SELECT COUNT(*) AS total FROM entradas";
            $query = $this->conexion->prepare($sql);
            $query->execute();

            // Verificamos si la consulta fue exitosa
            if ($query) {
                $result = $query->fetch(PDO::FETCH_ASSOC);
                $return["correcto"] = TRUE;
                $return["datos"] = $result["total"];  // El total de todas las entradas
            }
        } catch (PDOException $ex) {
            $return["error"] = $ex->getMessage();
        }

        return $return;
    }

    public function totalEntradasLog()
    {
        $return = [
            "correcto" => FALSE,
            "datos" => NULL,
            "error" => NULL
        ];

        try {
            $sql = "SELECT COUNT(*) AS total FROM logs";
            $query = $this->conexion->prepare($sql);
            $query->execute();

            // Verificamos si la consulta fue exitosa
            if ($query) {
                $result = $query->fetch(PDO::FETCH_ASSOC);
                $return["correcto"] = TRUE;
                $return["datos"] = $result["total"];  // El total de todas las entradas
            }
        } catch (PDOException $ex) {
            $return["error"] = $ex->getMessage();
        }

        return $return;
    }

    public function listadopagOrdenado($idUsuario, $pagina, $resultados_por_pagina, $orden)
    {


        $return = [
            "correcto" => FALSE,
            "datos" => NULL,
            "error" => NULL
        ];
        // Calcular el OFFSET
        $offset = ($pagina - 1) * $resultados_por_pagina;

        // Verificamos que el ID sea válido
        if ($idUsuario && is_numeric($idUsuario)) {
            try {

                // USER
                if ($_SESSION['usuario']['rol'] == 'user') {
                    $sql = "SELECT 
                        c.nombrecat AS categoria, 
                        e.ident,
                        e.titulo, 
                        e.imagen, 
                        e.descripcion, 
                        e.fecha, 
                        u.email, 
                        u.nombre, 
                        u.avatar, 
                        u.iduser
                    FROM entradas e
                    JOIN usuarios u ON e.idUsuario = u.iduser
                    JOIN categoria c ON e.idCategoria = c.idcat
                    WHERE u.iduser = :id
                    ORDER BY e.fecha $orden
                    LIMIT :limit OFFSET :offset";

                    $resultquery = $this->conexion->prepare($sql);
                    $resultquery->bindParam(':id', $idUsuario, PDO::PARAM_INT);
                    $resultquery->bindParam(':limit', $resultados_por_pagina, PDO::PARAM_INT);
                    $resultquery->bindParam(':offset', $offset, PDO::PARAM_INT);
                    $resultquery->execute();

                    //ADMIN
                } elseif ($_SESSION['usuario']['rol'] == 'admin') {

                    $sql = "SELECT 
                        c.nombrecat AS categoria, 
                        e.*,
                        u.email, 
                        u.nombre, 
                        u.avatar, 
                        u.iduser
                    FROM entradas e
                    JOIN usuarios u ON e.idUsuario = u.iduser
                    JOIN categoria c ON e.idCategoria = c.idcat
                    ORDER BY e.fecha $orden
                    LIMIT :limit OFFSET :offset";

                    $resultquery = $this->conexion->prepare($sql);
                    $resultquery->bindParam(':limit', $resultados_por_pagina, PDO::PARAM_INT);
                    $resultquery->bindParam(':offset', $offset, PDO::PARAM_INT);
                    $resultquery->execute();
                }

                if ($resultquery) {
                    $result = $resultquery->fetchAll(PDO::FETCH_ASSOC);
                    $return["correcto"] = TRUE;
                    $return["datos"] = $result;
                }
            } catch (PDOException $ex) {
                $return["error"] = $ex->getMessage();
            }
        }

        return $return;
    }

    /**
     * Funcion para ejecutar una consulta sql 
     * Creada para la ejecutar la tabla logs del ejercicio 2.13
     */
    public function ejecutarSQL($sql)
    {
        try {

            $query = $this->conexion->prepare($sql);
            $query->execute();
            // Verificamos si la consulta fue exitosa
            if ($query) {
                $return['correcto'] = true;
                $return["datos"] = $query->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $ex) {
            $return["error"] = $ex->getMessage();
        }

        return $return;
    }

    /**
     * Este método ejecutará el procedimiento almacenado cada vez que se llame.
     */
    public function registrarLog($usuario, $operacion)
    {
        try {
            $sql = "CALL InsertarLog(:usuario, :operacion)";
            $query = $this->conexion->prepare($sql);
            $query->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $query->bindParam(':operacion', $operacion, PDO::PARAM_STR);
            $query->execute();
            return true; // Éxito
        } catch (PDOException $ex) {
            return ["error" => $ex->getMessage()];
        }
    }

    /**
     * Listado por paginacion
     * 
     */
    public function listadopaglog($pagina = 1, $resultados_por_pagina = 4)
    {
        // Variable para devolver los resultados
        $return = ["correcto" => FALSE, "datos" => NULL, "error" => NULL];

        // Calcular el OFFSET
        $offset = ($pagina - 1) * $resultados_por_pagina;

        try {

            $sql = "SELECT *
                    FROM logs 
                    ORDER BY fecha DESC
                    LIMIT :limit OFFSET :offset";

            $resultquery = $this->conexion->prepare($sql);
            $resultquery->bindParam(':limit', $resultados_por_pagina, PDO::PARAM_INT);
            $resultquery->bindParam(':offset', $offset, PDO::PARAM_INT);
            $resultquery->execute();


            // Supervisamos si todo ha ido bien
            if ($resultquery) {
                $return['correcto'] = TRUE;
                $return['datos'] = $resultquery->fetchAll(PDO::FETCH_ASSOC);
                //var_dump($return['datos']);
            }
        } catch (Exception $ex) {
            $return['error'] = $ex->getMessage();
        }

        return $return;
    }

    public function delentlog($id)
    {
        // La función devuelve un array con dos valores:'correcto', que indica si la
        // operación se realizó correctamente, y 'mensaje', campo a través del cual le
        // mandamos a la vista el mensaje indicativo del resultado de la operación
        $return = [
            "correcto" => FALSE,
            "error" => NULL
        ];

        //Si hemos recibido el id y es un número realizamos el borrado...
        if ($id && is_numeric($id)) {
            try {
                //Inicializamos la transacción
                $this->conexion->beginTransaction();

                //Definimos la instrucción SQL parametrizada 
                $sql = "DELETE FROM logs WHERE id=:id";
                $query = $this->conexion->prepare($sql);
                $query->execute(['id' => $id]);

                //Supervisamos si la eliminación se realizó correctamente... 
                if ($query) {
                    $this->conexion->commit();  // commit() confirma los cambios realizados durante la transacción
                    $return["correcto"] = TRUE;
                } // o no :(
            } catch (PDOException $ex) {
                $this->conexion->rollback(); // rollback() se revierten los cambios realizados durante la transacción
                $return["error"] = $ex->getMessage();
            }
        } else {
            $return["correcto"] = FALSE;
        }

        return $return;
    }
}
