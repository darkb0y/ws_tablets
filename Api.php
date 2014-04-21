<?php
require_once ("Rest.php");
class Api extends Rest {
	const servidor = "10.177.73.120";
	const usuario_db = "ecommerce_user";
	const pwd_db = "ecommerce";
	const nombre_db = "cms0mxdb";
	private $_conn = NULL;
	private $_metodo;
	private $_argumentos;
	public function __construct() {
		parent::__construct();
		$this -> conectarDB();
	}

	//Conexion a BD
	private function conectarDB() {
		$dsn = 'mysql:dbname=' . self::nombre_db . ';host=' . self::servidor;
		try {
			$this -> _conn = new PDO($dsn, self::usuario_db, self::pwd_db);
		} catch (PDOException $e) {
			echo 'Falló la conexión: ' . $e -> getMessage();
		}
	}

	//Errores devueltos
	private function devolverError($id) {
		$errores = array( array('estado' => "error", "msg" => "Petición no encontrada"), 
							array('estado' => "error", "msg" => "Petición no aceptada"), 
							array('estado' => "error", "msg" => "Petición sin contenido"), 
							array('estado' => "error", "msg" => "Email o password incorrectos"), 
							array('estado' => "error", "msg" => "Error al borrar"), 
							array('estado' => "error", "msg" => "Error al actualizar"), 
							array('estado' => "error", "msg" => "Error en búsqueda"), 
							array('estado' => "error", "msg" => "Error creando usuario"), 
							array('estado' => "error", "msg" => "Usuario ya existe"));
		return $errores[$id];
	}

	public function procesarLLamada() {
		if (isset($_REQUEST['url'])) {
			//Eliminamos los '/' sobrantes de la URL
			$url = explode('/', trim($_REQUEST['url']));
			$url = array_filter($url);
			$this -> _metodo = strtolower(array_shift($url));
			$this -> _argumentos = $url;
			$func = $this -> _metodo;
			if ((int) method_exists($this, $func) > 0) {
				if (count($this -> _argumentos) > 0) {
					call_user_func_array(array($this, $this -> _metodo), $this -> _argumentos);
				} else {
					call_user_func(array($this, $this -> _metodo));
				}
			} else
				$this -> mostrarRespuesta($this -> convertirJson($this -> devolverError(0)), 404);
		}
		$this -> mostrarRespuesta($this -> convertirJson($this -> devolverError(0)), 404);
	}

	//Convierte a Json
	private function convertirJson($data) {
		return json_encode($data);
	}
	
	//Retorna usuarios
	private function usuarios() {
		if ($_SERVER['REQUEST_METHOD'] != "GET") {
			$this -> mostrarRespuesta($this -> convertirJson($this -> devolverError(1)), 405);
		}
		$query = $this -> _conn -> query("SELECT customer_id, email FROM TBL_IntCuenta");
		$filas = $query -> fetchAll(PDO::FETCH_ASSOC);
		$num = count($filas);
		if ($num > 0) {
			$respuesta['estado'] = 'Correcto';
			$respuesta['usuarios'] = $filas;
			$this -> mostrarRespuesta($this -> convertirJson($respuesta), 200);
		}
		$this -> mostrarRespuesta($this -> devolverError(2), 204);
	}

}

$api = new Api();
$api -> procesarLLamada();
?>