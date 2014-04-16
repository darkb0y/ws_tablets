<?php  
 require_once("Rest.php");  
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
     $this->conectarDB();  
   }  
   private function conectarDB() {  
     $dsn = 'mysql:dbname=' . self::nombre_db . ';host=' . self::servidor;  
     try {  
       $this->_conn = new PDO($dsn, self::usuario_db, self::pwd_db);  
     } catch (PDOException $e) {  
       echo 'Falló la conexión: ' . $e->getMessage();  
     }  
   }  
   private function devolverError($id) {  
     $errores = array(  
       array('estado' => "error", "msg" => "petición no encontrada"),  
       array('estado' => "error", "msg" => "petición no aceptada"),  
       array('estado' => "error", "msg" => "petición sin contenido"),  
       array('estado' => "error", "msg" => "email o password incorrectos"),  
       array('estado' => "error", "msg" => "error borrando usuario"),  
       array('estado' => "error", "msg" => "error actualizando nombre de usuario"),  
       array('estado' => "error", "msg" => "error buscando usuario por email"),  
       array('estado' => "error", "msg" => "error creando usuario"),  
       array('estado' => "error", "msg" => "usuario ya existe")  
     );  
     return $errores[$id];  
   }  
   public function procesarLLamada() {  
     if (isset($_REQUEST['url'])) {  
       //si por ejemplo pasamos explode('/','////controller///method////args///') el resultado es un array con elem vacios;
       //Array ( [0] => [1] => [2] => [3] => [4] => controller [5] => [6] => [7] => method [8] => [9] => [10] => [11] => args [12] => [13] => [14] => )
       $url = explode('/', trim($_REQUEST['url']));  
       //con array_filter() filtramos elementos de un array pasando función callback, que es opcional.
       //si no le pasamos función callback, los elementos false o vacios del array serán borrados 
       //por lo tanto la entre la anterior función (explode) y esta eliminamos los '/' sobrantes de la URL
       $url = array_filter($url);  
       $this->_metodo = strtolower(array_shift($url));  
       $this->_argumentos = $url;  
       $func = $this->_metodo;  
       if ((int) method_exists($this, $func) > 0) {  
         if (count($this->_argumentos) > 0) {  
           call_user_func_array(array($this, $this->_metodo), $this->_argumentos);  
         } else {//si no lo llamamos sin argumentos, al metodo del controlador  
           call_user_func(array($this, $this->_metodo));  
         }  
       }  
       else  
         $this->mostrarRespuesta($this->convertirJson($this->devolverError(0)), 404);  
     }  
     $this->mostrarRespuesta($this->convertirJson($this->devolverError(0)), 404);  
   }  
   private function convertirJson($data) {  
     return json_encode($data);  
   }  
   
   private function usuarios() {  
     if ($_SERVER['REQUEST_METHOD'] != "GET") {  
       $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);  
     }  
     $query = $this->_conn->query("SELECT salutation, fname, lname FROM TBL_IntDireccion");  
     $filas = $query->fetchAll(PDO::FETCH_ASSOC);  
     $num = count($filas);  
     if ($num > 0) {  
       $respuesta['estado'] = 'correcto';  
       $respuesta['usuarios'] = $filas;  
       $this->mostrarRespuesta($this->convertirJson($respuesta), 200);  
     }  
     $this->mostrarRespuesta($this->devolverError(2), 204);  
   }  
    
   private function login() {  
     if ($_SERVER['REQUEST_METHOD'] != "POST") {  
       $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);  
     }  
     if (isset($this->datosPeticion['email'], $this->datosPeticion['pwd'])) {  
    //el constructor del padre ya se encarga de sanear los datos de entrada  
       $email = $this->datosPeticion['email'];  
       $pwd = $this->datosPeticion['pwd'];  
       if (!empty($email) and !empty($pwd)) {  
         if (filter_var($email, FILTER_VALIDATE_EMAIL)) {  
           //consulta preparada ya hace mysqli_real_escape()  
           $query = $this->_conn->prepare("SELECT id, nombre, email, fRegistro FROM usuario WHERE   
           email=:email AND password=:pwd ");  
           $query->bindValue(":email", $email);  
           $query->bindValue(":pwd", sha1($pwd));  
           $query->execute();  
           if ($fila = $query->fetch(PDO::FETCH_ASSOC)) {  
             $respuesta['estado'] = 'correcto';  
             $respuesta['msg'] = 'datos pertenecen a usuario registrado';  
             $respuesta['usuario']['id'] = $fila['id'];  
             $respuesta['usuario']['nombre'] = $fila['nombre'];  
             $respuesta['usuario']['email'] = $fila['email'];  
             $this->mostrarRespuesta($this->convertirJson($respuesta), 200);  
           }  
         }  
       }  
     }  
     $this->mostrarRespuesta($this->convertirJson($this->devolverError(3)), 400);  
   }  
     
   private function actualizarNombre($idUsuario) {  
     if ($_SERVER['REQUEST_METHOD'] != "PUT") {  
       $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);  
     }  
     //echo $idUsuario . "<br/>";  
     if (isset($this->datosPeticion['nombre'])) {  
       $nombre = $this->datosPeticion['nombre'];  
       $id = (int) $idUsuario;  
       if (!empty($nombre) and $id > 0) {  
         $query = $this->_conn->prepare("update usuario set nombre=:nombre WHERE id =:id");  
         $query->bindValue(":nombre", $nombre);  
         $query->bindValue(":id", $id);  
         $query->execute();  
         $filasActualizadas = $query->rowCount();  
         if ($filasActualizadas == 1) {  
           $resp = array('estado' => "correcto", "msg" => "nombre de usuario actualizado correctamente.");  
           $this->mostrarRespuesta($this->convertirJson($resp), 200);  
         } else {  
           $this->mostrarRespuesta($this->convertirJson($this->devolverError(5)), 400);  
         }  
       }  
     }  
     $this->mostrarRespuesta($this->convertirJson($this->devolverError(5)), 400);  
   }  
     
   private function borrarUsuario($idUsuario) {  
     if ($_SERVER['REQUEST_METHOD'] != "DELETE") {  
       $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);  
     }  
     $id = (int) $idUsuario;  
     if ($id >= 0) {  
       $query = $this->_conn->prepare("delete from usuario WHERE id =:id");  
       $query->bindValue(":id", $id);  
       $query->execute();  
       //rowcount para insert, delete. update  
       $filasBorradas = $query->rowCount();  
       if ($filasBorradas == 1) {  
         $resp = array('estado' => "correcto", "msg" => "usuario borrado correctamente.");  
         $this->mostrarRespuesta($this->convertirJson($resp), 200);  
       } else {  
         $this->mostrarRespuesta($this->convertirJson($this->devolverError(4)), 400);  
       }  
     }  
     $this->mostrarRespuesta($this->convertirJson($this->devolverError(4)), 400);  
   }  
   private function existeUsuario($email) {  
     if (filter_var($email, FILTER_VALIDATE_EMAIL)) {  
       $query = $this->_conn->prepare("SELECT email from usuario WHERE email = :email");  
       $query->bindValue(":email", $email);  
       $query->execute();  
       if ($query->fetch(PDO::FETCH_ASSOC)) {  
         return true;  
       }  
     }  
     else  
       return false;  
   }  
     
   private function crearUsuario() {  
     if ($_SERVER['REQUEST_METHOD'] != "POST") {  
       $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);  
     }  
     if (isset($this->datosPeticion['nombre'], $this->datosPeticion['email'], $this->datosPeticion['pwd'])) {  
       $nombre = $this->datosPeticion['nombre'];  
       $pwd = $this->datosPeticion['pwd'];  
       $email = $this->datosPeticion['email'];  
       if (!$this->existeUsuario($email)) {  
         $query = $this->_conn->prepare("INSERT into usuario (nombre,email,password,fRegistro) VALUES (:nombre, :email, :pwd, NOW())");  
         $query->bindValue(":nombre", $nombre);  
         $query->bindValue(":email", $email);  
         $query->bindValue(":pwd", sha1($pwd));  
         $query->execute();  
         if ($query->rowCount() == 1) {  
           $id = $this->_conn->lastInsertId();  
           $respuesta['estado'] = 'correcto';  
           $respuesta['msg'] = 'usuario creado correctamente';  
           $respuesta['usuario']['id'] = $id;  
           $respuesta['usuario']['nombre'] = $nombre;  
           $respuesta['usuario']['email'] = $email;  
           $this->mostrarRespuesta($this->convertirJson($respuesta), 200);  
         }  
         else  
           $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);  
       }  
       else  
         $this->mostrarRespuesta($this->convertirJson($this->devolverError(8)), 400);  
     } else {  
       $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);  
     }  
   }  
 }  
 $api = new Api();  
 $api->procesarLLamada(); 
 
 ?>