<?php

include('application/libraries/mustache.php');

class Framework {
  private static $controller;
  private static $action;
  private static $scope;
  private static $rendered;

  function Route() {
    $url = $_SERVER['REQUEST_URI'];
    $segments = explode('/', $url);
    $controller = $segments[1] ?: 'home';
    $action = $segments[2] ?: 'index';

    $controller_file = 'application/controllers/'.$controller.'.php';

    if (!file_exists($controller_file))
      Framework::NotFound('Controller file not found');

    include($controller_file);

    if (!class_exists($controller))
      Framework::NotFound('Controller class not found.');

    $class = new $controller;
    
    if (!in_array(strtolower($action), array_map('strtolower', get_class_methods($class))))
    {
      if (!in_array('index', array_map('strtolower', get_class_methods($class))))
        not_found();
      $action = 'index';
    }

    $class->url = $url;
    $class->segments = $segments;

    self::$controller = $controller;
    self::$action = $action;
    self::$scope = $class;

    $parameters = array_slice($segments, $action == $segments[2] ? 3 : 2);

    call_user_func_array(array($class, $action), $parameters);
    
    if (self::$rendered == false)
      self::Render();
  }

  function Render($view = null, $data = null) {
    $view = $view ?: self::$controller.'_'.self::$action;
    $data = $data ?: get_object_vars(self::$scope) ?: array();

    if (file_exists('application/views/'.$view.'.html'))
    {
      // 'Readme' for Mustache: http://blog.couchone.com/post/622014913/mustache-js

      $partials = array();
      if ($handle = opendir('application/views/')) {
          while (false !== ($file = readdir($handle))) {
              if (strncmp($file, 'partial_', strlen('partial_')) == 0) {
                $partials[substr($file, strlen('partial_'), -strlen('.html'))] = file_get_contents('application/views/'.$file);
              }
          }
          closedir($handle);
      }

      $mustache = new Mustache;
      $template = file_get_contents('application/views/'.$view.'.html');
      echo $mustache->render($template, $data, $partials);
    }
    else if (file_exists('application/views/'.$view.'.php'))
    {
      foreach(array_keys($data) as $var)
        $$var = $data[$var];
      include('application/views/'.$view.'.php');
    }
    else
    {
      Framework::NotFound();
    }

    self::$rendered = true;
  }

  function Load($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response);
  }

  function NotFound($error = 'The resource you\'re asking for isn\'t avaliable.') {
    header("HTTP/1.0 404 Not Found");
    echo $error;
    exit();
  }
}

?>
