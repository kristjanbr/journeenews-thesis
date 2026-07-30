<?php

class ViewHelper {

    //Displays a given view and sets the $variables array into scope.
    public static function render($file, $variables = array(), $statusCode = 200) {
        http_response_code($statusCode);

        extract($variables);

        ob_start();
        include($file);
        $renderedView = ob_get_clean();

        echo $renderedView;
    }

    // Redirects to the given URL
    public static function redirect($url) {
        header("Location: " . $url);
    }

    // Returns true if the request is AJAX
    public static function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
