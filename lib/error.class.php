<?php

class Error {

  public static function error_handler($level, $message, $file, $line) {
    if(error_reporting() !== 0) {
      throw new \ErrorException($message, 0, $level, $file, $line);
    }
  }

  public static function exception_handler($e) {
    $code = $e->getCode();

    if($code !== 404) {
      $code = 500;
    }
    http_response_code($code);

    echo '<h1>Fatal error</h1>';
    echo '<p>Uncaught exception: '  . get_class($e) . '</p>';
    echo '<p>Message: '  . $e->getMessage() . '</p>';
    echo '<p>Stack trace: <pre>'  . $e->getTraceAsString() . '</pre></p>';
    echo '<p>Thrown in: '  . $e->getFile() . ' on line ' . $e->getLine() . '</p>';
  }

}

?>
