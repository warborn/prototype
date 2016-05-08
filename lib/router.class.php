<?php

class Router {

  /**
   * Associative array of route (the routing table)
   * @var array
   *
   */
   protected $routes = [];

  /**
   * Parameters from the matched route
   *
   * @var array
   */
  protected $params = [];

  /**
   * Add a route to the routing table
   *
   * @param string $route The route URL
   * @param array $params Paramethers (controller, action, etc)
   * @return void
   */
   public function add($route, $method, $options = []) {
     // Convert the route to a regular expression: escape forward stripslashes
     $route = preg_replace('/\//', '\\/', $route);

     // Convert variables e.g. {controller}
     $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

     // Convert variables with custom regular expressions e.g. {id}
     $route = preg_replace('/\{:([a-z_]+)\}/', '(?P<\1>\w+)', $route);

     // Add the start and end delimeters, and case insensitive finfo_set_flags
     $route = '/^' . $route . '$/i';

     $parms = [];
     if (isset($options["to"])) {
       $params = $this->get_parse_controller_action($options["to"]);
     }

     $this->routes[] = array('route' => $route, 'method' => $method, 'params' => $params);
   }

   /**
    * Add the routes for the eight standar actions unless only or except option are passed
    *
    * @param string $resource
    * @param array $options
    * @return void
    */
   public function resources($resource, $options = []) {
     $actions = ['index' => 'GET', 'show' => 'GET', 'new' => 'GET', 'create' => 'POST', 'edit' => 'GET', 'update' => 'PATCH', 'delete' => 'GET', 'destroy' => 'DELETE'];

     if(isset($options['only'])) {
       $actions = array_intersect($actions, $options['only']);
     } else if(isset($options['except'])) {
       $actions = array_diff($actions, $options['except']);
     }

     foreach ($actions as $action => $method) {
       if($action === 'index') {
         $this->add($resource, $method, ['to' => $resource . '#' . $action]);
         $this->add($resource . '/' . $action, $method, ['to' => $resource . '#' . $action]);
       } else if(in_array($action, ['new', 'create'])) {
         $this->add($resource . '/' . $action, $method, ['to' => $resource . '#' . $action]);
       } else if(in_array($action, ['show', 'edit', 'update', 'delete', 'destroy'])) {
         $this->add($resource . '/{:id}/' . $action, $method, ['to' => $resource . '#' . $action]);
       }
     }
   }

   /**
    * Get all the routes from the routing table
    *
    * @return array
    */
    public function get_routes() {
      return $this->routes;
    }

  /**
   * Match the route in the routing table, setting the $params property if a route is found
   *
   * @param string $url The route URL
   * @return boolean true if match found, false otherwise
   */
   public function match($url, $method) {
    foreach ($this->routes as $route) {
      if($route['method'] === $method && preg_match($route['route'], $url, $matches)) {
        $match_params = [];

        foreach ($matches as $key => $match) {
          if(is_string($key)) {
            $match_params[$key] = $match;
          }
        }
        $this->params = array_merge($route['params'], $match_params);
        return true;
      }
    }

    return false;
  }

  /**
   * Get all the params
   *
   * @return array
   */
  public function get_params() {
    return $this->params;
  }

  /**
  * Dispatch the route, creating the controller object and running the action method
  *
  * @param string $url The route URL
  * @return void
  */
  public function dispatch($url, $method) {

    $url = $this->remove_query_string_variables($url);

    if($this->match($url, $method)) {
      $controller = $this->params['controller'];
      $controller = $this->convert_to_studly_caps($controller) . 'Controller';
      if(class_exists($controller)) {
        $controller_object = new $controller($this->params);

        $action = $this->params['action'];
        $action = $this->convert_to_snake_case($action);

        if(is_callable([$controller_object, $action])) {
          $data = $controller_object->$action();
          $view_path = ROOT.DS.'app'.DS.'views'.DS.str_replace('controller', '', strtolower($controller)).DS.$action.'.php';
          $view_object = new View($view_path, $data);
          $content = $view_object->render();
          
          $layout_path = ROOT.DS.'app'.DS.'views'.DS.'layouts'.DS.$controller_object->get_layout().'.html';
          $layout_object = new View($layout_path, array('yield' => $content));
          echo $layout_object->render();
        } else {
          throw new \Exception("Undefine action $action in $controller");
        }
      } else {
        throw new \Exception("Undefined controller $controller");
      }
    } else {
      $url = $url === '' ? '/' : $url;
      throw new \Exception('No route matches ' . $method . ' [' . $url . ']', 404);
    }
  }

  /**
  * Convert the string with hyphens to StudlyCaps
  * e.g. post-authors => PostAuthors
  *
  * @param string $string
  * @return string
  */
  public function convert_to_studly_caps($string) {
    return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
  }

  /**
  * Convert the string with hyphens to snake_case
  * e.g. add-new => add_new
  *
  * @param string $string
  * @return string
  */
  public function convert_to_snake_case($string) {
    return str_replace('-', '_', $string);
  }

  /**
  * localhost/?page=1                 page=1                  ''
  * localhost/posts?page=1            posts&page=1            posts
  * localhost/posts/index?page=1      posts/index&page=1      posts/index
  *
  * @param string $url The full URL
  * @return string The URL with the query string variables removed
  */
  protected function remove_query_string_variables($url) {
    if($url != '') {
      $parts = explode('&', $url, 2);

      if(strpos($parts[0], '=') === false) {
        $url = $parts[0];
      } else {
        $url = '';
      }
    }

    return $url;
  }

  /**
   * Extracts the controller and action from a string e.g. 'posts#show'
   *
   * @param string $string
   * @return array Contains the controller and action as an associative array
   */
  protected function get_parse_controller_action($string) {
    $parts = explode('#', $string);

    if(count($parts) == 2) {
      return array('controller' => $parts[0], 'action' => $parts[1]);
    } else {
      throw new \Exception('Cannot parse $string into controller and action');
    }
  }

}

?>
