<?php
require_once 'controladores/controlador.php'; //Incluimos metodos del controlador

//Definimos un objeto controlador
$controlador = new controlador();


//Vemos si nos llega por em método GET algun parámetro
if ($_GET && $_GET['accion']) {
    //Sanitizamos los dato que recibamos mediante el GET+
    $accion = filter_input(INPUT_GET, 'accion', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    //convierte caracteres especiales en entidades HTML, previniendo inyecciones de código malicioso.
    echo $accion;

    //Verificamos que el objeto controlador que hemos creado implementa el 
    //método que le hemos asignado mediqante el GET
    if (method_exists($controlador, $accion)) {

        $controlador->$accion(); //Ejecutamos la acccion que indicamos 
    } else {
        $controlador->index(); //Si no, redirigimos a la pag inicio
    }
} else {
    $controlador->index(); //Redirigimos a la pag inicio inicialmente
}

?>

<!--
* El acceso a la aplicación se realiza desde el fichero index.php, script que se encargará de invocar los métodos del controlador asociados a lo senlaces pulsados en la vista inicio.php.

* index.php definirá un controlador que nos permitirá acceder a todos sus métodos.

* Inicialmente, al acceder a index.php, como no le estaríamos pasando ningún parámetro mediante
GET,se ejecutaría,por defecto,el método index() del controlador, método que únicamente asigna el
título a la vista inicio.php y la muestra mediante el include_once de inicio.php.

* Si pulsamos en la vista inicio.php sobre alguno de los enlacesde operación (listado o alta), ésta nos redirigirá a la página index.php pasándole como parámetro URL (GET) la acción elegida (listado o adduser) a través del parámetro ‘acción’.

* Al acceder a index.php con el parámetro GET[‘accion’], en primer lugar, para evitar posibles
inyecciones de código, se sanitizaría el valor recibido y, a continuación, tratamos de ejecutar el
método del controlador cuyo nombre coincide con el de la acción. Señalar que en la instrucción:
$controlador->$accion(); estaríamos usando el valor de una variable ($accion) como nombre
de una función, simplemente añadiendo el par de paréntesis del final. -->