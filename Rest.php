<?php
/*
 * Devuelve las cabeceras con el código de estado y el resultado de la petición.
 * Filtra los datos enviados en la petición.
 * */
class Rest {
	//public $tipo = "application/json";
	public $datosPeticion = array();
	private $_codEstado = 200;
	
	public function __construct() {
		$this -> tratarEntrada();
	}
	
	//Recibe respuesta Json ($data) y código de estado HTTP($estado)
	public function mostrarRespuesta($data, $estado, $tipo) {
		$this -> _codEstado = ($estado) ? $estado : 200; //Sino se envía $estado, por defecto será 200
		$this -> setCabecera($tipo);
		print_r($data);
		exit;
	}

	//Envia cabeceras
	private function setCabecera($tipo) {
		header("HTTP/1.1 " . $this -> _codEstado . " " . $this -> getCodEstado());
		header("Content-Type:" . $tipo . ';charset=utf-8');
	}

	//Método recursivo para tratar valores recibidos
	private function limpiarEntrada($data) {
		$entrada = array();
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$entrada[$key] = $this -> limpiarEntrada($value);
			}
		} else {
			if (get_magic_quotes_gpc()) {
				$data = trim(stripslashes($data));
			}
			//Elimina etiquetas y convierte caracteres a entidades HTML
			$data = htmlentities($data);
			$entrada = trim($data);
		}
		return $entrada;
	}

	//Examina datos de entrada
	private function tratarEntrada() {
		$metodo = $_SERVER['REQUEST_METHOD'];
		switch ($metodo) {
			case "GET" :
				$this -> datosPeticion = $this -> limpiarEntrada($_GET);
				break;
			case "POST" :
				$this -> datosPeticion = $this -> limpiarEntrada($_POST);
				break;
			default :
				$this -> response('', 404);
				break;
		}
	}
	
	//Posibles códigos de estado
	private function getCodEstado() {
		$estado = array(200 => 'OK', 
						201 => 'Created', 
						202 => 'Accepted', 
						204 => 'No Content', 
						301 => 'Moved Permanently', 
						302 => 'Found', 
						303 => 'See Other', 
						304 => 'Not Modified', 
						400 => 'Bad Request', 
						401 => 'Unauthorized', 
						403 => 'Forbidden', 
						404 => 'Not Found', 
						405 => 'Method Not Allowed', 
						500 => 'Internal Server Error');
		$respuesta = ($estado[$this -> _codEstado]) ? $estado[$this -> _codEstado] : $estado[500];
		return $respuesta;
	}

}
?>