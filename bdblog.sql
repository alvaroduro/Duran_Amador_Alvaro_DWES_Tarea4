-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 11-02-2025 a las 17:01:16
-- Versión del servidor: 8.0.31
-- Versión de PHP: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bdblog`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE IF NOT EXISTS `categoria` (
  `idcat` int NOT NULL AUTO_INCREMENT,
  `nombrecat` varchar(40) NOT NULL,
  PRIMARY KEY (`idcat`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`idcat`, `nombrecat`) VALUES
(1, 'accesorios'),
(2, 'consolas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas`
--

DROP TABLE IF EXISTS `entradas`;
CREATE TABLE IF NOT EXISTS `entradas` (
  `ident` int NOT NULL AUTO_INCREMENT,
  `idUsuario` int NOT NULL,
  `idCategoria` int NOT NULL,
  `titulo` varchar(40) NOT NULL,
  `imagen` varchar(40) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` timestamp NOT NULL,
  PRIMARY KEY (`ident`),
  KEY `idUsuario` (`idUsuario`),
  KEY `idCategoria` (`idCategoria`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `entradas`
--

INSERT INTO `entradas` (`ident`, `idUsuario`, `idCategoria`, `titulo`, `imagen`, `descripcion`, `fecha`) VALUES
(1, 1, 1, 'Titulo', 'foto1.jpg', 'Esta es la descripcion', '2023-01-17 10:47:08'),
(13, 2, 1, 'Titulo Entrada 3', '1676983922-foto3.jpg', '<h1><strong>Hola :)</strong></h1>\n\n<ul>\n	<li><strong>Esto es una pba</strong></li>\n	<li><strong>Esto es una pba</strong></li>\n</ul>\n', '2023-02-21 11:52:02'),
(14, 2, 2, 'Titulo Entrada 5', '1676984080-foto2.jpg', '<h2 style=\"font-style:italic;\"><strong>Otra Pba ;)</strong></h2>\n\n<ol>\n	<li>:O</li>\n	<li>:)</li>\n</ol>\n', '2023-02-21 11:54:40'),
(15, 1, 1, 'Titulo Entrada 4', '1676984400-foto5.jpg', '<h1><strong>Soy admin ;)</strong></h1>\r\n\r\n<ul>\r\n	<li>S&iacute; se&ntilde;or</li>\r\n	<li>No s&eacute;</li>\r\n</ul>\r\n\r\n<p>&nbsp;</p>\r\n', '2023-02-21 12:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `iduser` int NOT NULL AUTO_INCREMENT,
  `nick` varchar(40) NOT NULL,
  `nombre` varchar(40) NOT NULL,
  `apellidos` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `contrasenia` varchar(40) NOT NULL,
  `avatar` varchar(50) NOT NULL,
  `rol` varchar(40) NOT NULL,
  PRIMARY KEY (`iduser`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`iduser`, `nick`, `nombre`, `apellidos`, `email`, `contrasenia`, `avatar`, `rol`) VALUES
(1, 'malodo', 'maria', 'Lopez Dominguez', 'maria@gmail.com', '123', 'avatar1.png', 'admin'),
(2, 'ninja', 'antonio', 'gonzalez', 'antonio@gmail.com', '12345', 'avatar2.png', 'user');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `entradas_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`iduser`) ON UPDATE CASCADE,
  ADD CONSTRAINT `entradas_ibfk_2` FOREIGN KEY (`idCategoria`) REFERENCES `categoria` (`idcat`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
