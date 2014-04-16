<?php
// Incluye controlador y vista
include('views.php');
include('controllers.php');

// Elegir formato de respuesta
$accept = explode(',', $_SERVER['HTTP_ACCEPT']);
if($accept[0] == 'text/html') {
    $view = new HtmlView();
} elseif($accept[0] == 'text/xml') {
    $view = new XmlView();
} else {
    $view = new JsonView();
}

// Recibe parámetros
$verb = $_SERVER['REQUEST_METHOD'];
$data = array();
switch($verb) {
    case 'GET':
        parse_str($_SERVER['QUERY_STRING'], $data);
        break;
    case 'POST':
    case 'PUT':
        // Parámetros JSON, matríz
        //$data = json_decode(file_get_contents('php://input'), true);
        break;
    case 'DELETE':
        // do nothing
        break;
    default:
        // WTF?
        break;
}

// Enrutar solicitud
$path = explode('/',$_SERVER['PATH_INFO']);
$action_name = strtoupper($verb) . 'Action';
$controller_name = ucfirst($path[1]) . 'Controller';
$controller = new $controller_name();
$data = $controller->$action_name($path, $data);

// output appropriately
$view->render($data);
?>