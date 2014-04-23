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
							array('estado' => "error", "msg" => "Usuario ya existe"),
							array('estado' => "error", "msg" => "Datos de dirección incorrectos")); 
		return $errores[$id];
	}

	//Método inicial que atiende la petición
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
				list ($data, $tipo) = $this -> convertirFormato($this -> devolverError(0));
				$this -> mostrarRespuesta($data, 404, $tipo);
		}
		list ($data, $tipo) = $this -> convertirFormato($this -> devolverError(0));
		$this -> mostrarRespuesta($data, 404, $tipo);
	}

	//Convierte 
	private function convertirFormato($data) {
		$accept = explode(',', $_SERVER['HTTP_ACCEPT']);
		//HTML
		if($accept[0] == 'text/htmlsss') {
			$header_content = header('text/html');
		    $data = $data;
        	return array($data, $header_content); 
		} 
		//XML
		elseif($accept[0] == 'text/xml') {
		   $simplexml = simplexml_load_string('<?xml version="1.0" ?><data />');
		   foreach($data as $key => $value) {
	            $simplexml->addChild($key, $value);
			}
			$header_content = header('text/xml');
	        return array($simplexml->asXML(), $header_content);
		} 
		//JSON
		else {
			$header_content = header('application/json');
		    return array(json_encode($data), $header_content); 
		}
	}

	//Retorna usuarios
	private function mostrarUsuarios() {
		if ($_SERVER['REQUEST_METHOD'] != "GET") {
			list ($data, $tipo) = $this -> convertirFormato($this -> devolverError(1));
			$this -> mostrarRespuesta($data, 405, $tipo);
		}
		$query = $this -> _conn -> query("SELECT customer_id, email FROM TBL_IntCuenta");
		$filas = $query -> fetchAll(PDO::FETCH_ASSOC);
		$num = count($filas);
		if ($num > 0) {
			$respuesta['estado'] = 'correcto';
			$respuesta['usuarios'] = $filas;
			list ($data, $tipo) =$this -> convertirFormato($respuesta);
			$this -> mostrarRespuesta($data, 200, $tipo);
		}
		list ($data, $tipo) = $this -> devolverError(2);
		$this -> mostrarRespuesta($data, 204, $tipo);
	}
	
	//Login por dirección
	 private function loginDireccion() {  
     if ($_SERVER['REQUEST_METHOD'] != "POST") {
     	list ($data, $tipo) = $this -> convertirFormato($this -> devolverError(1));
		$this -> mostrarRespuesta($data, 405, $tipo);
     }  
       list ($salutation, $fname, $lname, $address1, $zip, $city, $state, $pais) = $this -> validaDatos();
       if (!empty($salutation) and !empty($fname) and !empty($lname) and !empty($address1) and !empty($zip) and !empty($city) and !empty($state) and !empty($pais) ) {  
           $query = $this->_conn->prepare("SELECT customer_id FROM TBL_IntDireccion 
           									WHERE salutation=:salutation AND fname=:fname AND lname=:lname AND address1=:address1 AND zip=:zip AND city=:city AND state=:state AND pais=:pais ");  
           $query->bindValue(":salutation", $salutation);  
           $query->bindValue(":fname", $fname); 
		   $query->bindValue(":lname", $lname);  
		   $query->bindValue(":address1", $address1);  
		   $query->bindValue(":zip", $zip);  
		   $query->bindValue(":city", $city);  
		   $query->bindValue(":state", $state);  
		   $query->bindValue(":pais", $pais);  
           $query->execute();  
           if ($fila = $query->fetch(PDO::FETCH_ASSOC)) {
               $respuesta['producto']['customer_id'] = $fila['customer_id'];
			   //Devuelve información de la suscripción
			   $query2 = $this->_conn->prepare("SELECT customer_id, oc_id, suscription_id, description FROM TBL_IntProducto 
			   									WHERE customer_id=:customer_id ");  
	           $query2->bindValue(":customer_id", $respuesta['producto']['customer_id']);  
	           $query2->execute();  
	           if ($fila = $query->fetch(PDO::FETCH_ASSOC)) {
	           	 $respuesta['estado'] = 'correcto';  
	             $respuesta['msg'] = 'datos pertenecen a usuario registrado';  
	             $respuesta['producto']['oc_id'] = $fila['oc_id'];  
	             $respuesta['producto']['suscription_id'] = $fila['suscription_id'];  
	             $respuesta['producto']['description'] = $fila['description'];  
	             list ($data, $tipo) = $this -> convertirFormato($respuesta);
				 $this -> mostrarRespuesta($data, 200, $tipo);
			   }
           }  
       }  
	 list ($data, $tipo) = $this -> devolverError(9);
	 $this -> mostrarRespuesta($data, 400, $tipo);
   }

	//Método para validar datos de registro/login
	private function validaDatos()
	{
		$datos = array();
		//Nombre
		if (array_key_exists('salutation', $_POST)) {
			if (preg_match('/^[A-Z \'.-áéíóúÁÉÍÓÚÑñ]{2,30}$/i', $_POST['salutation'])) {
				$datos->salutation = $this->datosPeticion['salutation'];  
			}
		}
		//Apellido Paterno
		if (array_key_exists('fname', $_POST)) {
			if(preg_match('/^[A-Z \'.-áéíóúÁÉÍÓÚÑñ]{1,30}$/i', $_POST['fname'])) {
				$datos->fname = $this->datosPeticion['fname'];
			}
		}
		//Apellido materno
		if (array_key_exists('lname', $_POST)) {
			if (preg_match('/^[A-Z \'.-áéíóúÁÉÍÓÚÑñ]{1,30}$/i', $_POST['lname'])) {
				$datos->lname = $this->datosPeticion['lname']; 
			}
		}
		//Calle
		if (array_key_exists('address1', $_POST)) {	//Calle
			if (preg_match('/^[A-Z0-9áéíóúÁÉÍÓÚÑñ \'.-]{1,50}$/i', $_POST['address1'])) {
				$datos->address1 = $this->datosPeticion['address1'];
			} 
		}
		//Código postal
		if (array_key_exists('zip', $_POST)) {
			if (preg_match('/^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$/', $_POST['zip'])) {
				$datos->zip = $this->datosPeticion['zip']; 
			}
		}
		//Ciudad
		if (array_key_exists('city', $_POST) && !empty($_POST['city'])) {
			if (preg_match('/^[A-Z \'.-áéíóúÁÉÍÓÚÑñ]{1,30}$/i', $_POST['city'])) {
				$datos->city = $this->datosPeticion['city'];
			}
		}
		//Estado
		if (array_key_exists('state', $_POST) && !empty($_POST['state'])) {
			$datos->state = $this->datosPeticion['state']; 
		}
		//Pais
		if (array_key_exists('pais', $_POST)) {
			if (preg_match('/^[A-Z \'.-áéíóúÁÉÍÓÚÑñ]{1,30}$/i', $_POST['pais'])) {
				$datos->pais = $this->datosPeticion['pais']; 
			}
		}
		//Email
		if (array_key_exists('email', $_POST)) {
			if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				$datos->email = $this->datosPeticion['email']; 
			}
		}
	
		return array($datos);
	}

}

//Inicio
$api = new Api();
$api -> procesarLLamada();
?>