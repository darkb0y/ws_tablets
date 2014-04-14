<?php
// include the various view classes
include('views.php');
include('controllers.php');

// work out what format they want
$accept = explode(',', $_SERVER['HTTP_ACCEPT']);
if($accept[0] == 'text/html') {
    $view = new HtmlView();
} elseif($accept[0] == 'text/xml') {
    $view = new XmlView();
} else {
    $view = new JsonView();
}

// grab incoming parameters
$verb = $_SERVER['REQUEST_METHOD'];
$data = array();
switch($verb) {
    case 'GET':
        parse_str($_SERVER['QUERY_STRING'], &$data);
        break;
    case 'POST':
    case 'PUT':
        // incoming JSON data, it's an array
        $data = json_decode(file_get_contents('php://input'), true);
        break;
    case 'DELETE':
        // do nothing
        break;
    default:
        // WTF?
        break;
}

// route the request
$path = explode('/',$_SERVER['PATH_INFO']);
$action_name = strtoupper($verb) . 'Action';
$controller_name = ucfirst($path[1]) . 'Controller';
$controller = new $controller_name();
$data = $controller->$action_name($path, $data);

// output appropriately
$view->render($data);
?>