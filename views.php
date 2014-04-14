<?php

class ApiView {
}

class JsonView extends ApiView {
    public function render($content) {
        header('Content-Type: application/json');
        echo json_encode($content);
        return true;
    }
}

class HtmlView extends ApiView {
    public function render($content) {
        header('Content-Type: text/html');
        echo "<pre>";
        print_r($content);
        echo "</pre>";
        return true;
    }
}

class XmlView extends ApiView {
    public function render($content) {
        $simplexml = simplexml_load_string('<?xml version="1.0" ?><data />');
        foreach($content as $key => $value) {
            $simplexml->addChild($key, $value);
        }

        header('Content-Type: text/xml');
        echo $simplexml->asXML();
        return true;
    }
}
?>