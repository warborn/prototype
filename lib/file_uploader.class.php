<?php

class FileUploader {
  protected static $destination;
  protected static $messages = [];
  protected static $max_size = 51200;
  protected static $new_name;
  protected static $type_checking = true;
  protected static $rename_duplicates;
  protected static $throw_exceptions = false;
  protected static $allow_empty_file = false;
  protected static $moved_file_name = null;
  protected static $allowed_types = [
    'image/jpeg',
    'image/jpg',
    'image/png'
  ];
  protected static $not_trusted = [
    'bin', 'cgi', 'exe', 'js', 'php', 'py', 'sh', 'pl'
  ];
  protected static $suffix = '.upload';

  public static function initialize($upload_folder) {
    if(!is_dir($upload_folder) || !is_writable($upload_folder)) {
      throw new \Exception($upload_folder . ' must exists or must be writable.');
    }
    if($upload_folder[strlen($upload_folder) - 1] != '/') {
      $upload_folder .= '/';
    }
    self::$destination = $upload_folder;
  }

  public static function throw_exceptions($throw = false) {
    self::$throw_exceptions = $throw;
  }

  public static function allow_empty_file($allow = false) {
    self::$allow_empty_file = $allow;
  }

  public static function set_max_size($bytes) {
    $server_max_size = self::convert_to_bytes(ini_get('upload_max_filesize'));
    if($bytes > $server_max_size) {
      throw new \Exception('Maximum size cannot exceed server limit for individual files: ' . self::convert_from_bytes($server_max_size));
    }
    if(is_numeric($bytes) && $bytes > 0) {
      static::$max_size = $bytes;
    }
  }

  public static function allow_all_types($suffix = null  ) {
    self::$type_checking = false;
    if(!is_null($suffix)) {
      if(strpos($suffix, '.') === 0 || $suffix === '') {
        self::$suffix = $suffix;
      } else {
        self::$suffix = ".$suffix";
      }
    }
  }

  public static function get_file_name() {
    return self::$moved_file_name;
  }

  public static function upload($rename_duplicates = true) {
    self::$rename_duplicates = $rename_duplicates;
    $uploaded = current($_FILES);
    if(is_array($uploaded['name'])) {
      foreach ($uploaded['name'] as $index => $value) {
        $current_file['name'] = $uploaded['name'][$index];
        $current_file['type'] = $uploaded['type'][$index];
        $current_file['tmp_name'] = $uploaded['tmp_name'][$index];
        $current_file['error'] = $uploaded['error'][$index];
        $current_file['size'] = $uploaded['size'][$index];
        if(self::check_file($current_file)) {
          self::move_file($current_file);
          return true;
        }
      }
    } else {
      if(self::check_file($uploaded)) {
        self::move_file($uploaded);
        return true;
      }
    }
    return false;
  }

  public static function get_messages() {
    return self::$messages;
  }

  public static function convert_to_bytes($value) {
    $value = trim($value);
    $last_char = strtolower($value[strlen($value) - 1]);
    if(in_array($last_char, ['g', 'm', 'k'])) {
      switch($last_char) {
        case 'g':
          $value *= 1024;
        case 'm':
          $value *= 1024;
        case 'k':
          $value *= 1024;
      }
    }
    return $value;
  }

  public static function convert_from_bytes($bytes) {
    $bytes /= 1024;
    if($bytes > 1024) {
      return number_format($bytes / 1024, 1) . ' MB';
    } else {
      return number_format($bytes, 1) . ' KB';
    }
  }

  protected static function check_file($file) {
    if($file['error'] != 0) {
      self::set_error_message($file);
      return false;
    }
    if(!self::check_size($file)) {
      return false;
    }
    if(self::$type_checking === true) {
      if(!self::check_type($file)) {
        return false;
      }
    }
    self::check_name($file);
    return true;
  }

  protected static function move_file($file) {
    $filename = isset(self::$new_name) ? self::$new_name : $file['name'];
    $success = move_uploaded_file($file['tmp_name'], self::$destination . $filename);
    if($success) {
      self::$moved_file_name = $filename;
      $result = $file['name'] . ' was uploaded successfully';
      if(!is_null(self::$new_name)) {
        $result .= ', and was renamed as ' . self::$new_name;
      }
      $result .= '.';
      self::$messages[] = $result;
    } else {
      if(self::$throw_exceptions) {
        throw new \Exception('Could not upload ' . $file['name']);
      }
      self::$messages[] = 'Could not upload ' . $file['name'];
    }
  }

  public static function remove_file($filename) {
    $filepath = self::$destination.$filename;
    if(! empty($filename) && file_exists($filepath)) {
      if(!unlink($filepath)) {
        throw new \Exception('Unable to remove the file.');
      }
    } 
  }

  protected static function set_error_message($file) {

    switch($file['error']) {
      case 1:
      case 2:
        $message = $file['name'] . ' is to big: (max: ' . self::convert_from_bytes(self::$max_size) . ').';
        break;
      case 3:
        $message = $file['name'] . ' was only partially uploaded.';
        break;
      case 4:

        $message = 'No file was submitted.';
        break;
      default:
        $message = 'Sorry, there was a problem uploading ' . $file['name'];
        break;
    }
    if(self::$throw_exceptions && !self::$allow_empty_file) {
      throw new \Exception($message);
    }
    $messages[] = $message;
  }

  protected static function check_size($file) {
    if($file['size'] == 0) {
      if(self::$throw_exceptions) {
        throw new \Exception($file['name'] . ' is empty.');
      }
      self::$messages[] = $file['name'] . ' is empty.';
      return false;
    } elseif($file['size'] > self::$max_size) {
      if(self::$throw_exceptions) {
        throw new \Exception($file['name'] . ' exceeds the maximum size for a file (max: ' . self::convert_from_bytes(self::$max_size) . ').');
      }
      self::$messages[] = $file['name'] . ' exceeds the maximum size for a file (max: ' . self::convert_from_bytes(self::$max_size) . ').';
      return false;
    } else {
      return true;
    }
  }

  protected static function check_type($file) {
    if(in_array($file['type'], self::$allowed_types)) {
      return true;
    } else {
      if(self::$throw_exceptions) {
        throw new \Exception($file['name'] . ' is not a permitted type of file.');
      }
      self::$messages[] = $file['name'] . ' is not a permitted type of file.';
      return false;
    }
  }

  protected static function check_name($file) {
    self::$new_name = null;
    $filename_without_spaces = str_replace(' ', '_', $file['name']);
    if($filename_without_spaces != $file['name']) {
      self::$new_name = $filename_without_spaces;
    }

    $nameparts = pathinfo($filename_without_spaces);
    $extension = isset($nameparts['extension']) ? $nameparts['extension'] : '';
    if(!self::$type_checking && !empty(self::$suffix)) {
      if(in_array($extension, self::$not_trusted) || empty($extension)) {
        self::$new_name = $filename_without_spaces . self::$suffix;
      }
    }

    if(self::$rename_duplicates) {
      $name = isset(self::$new_name) ? self::$new_name : $file['name'];
      $existing_files = scandir(self::$destination);
      if(in_array($name, $existing_files)) {
        $i = 1;
        do {
          self::$new_name = $nameparts['filename'] . '_' . $i++;
          if(!empty($extension)) {
            self::$new_name .= ".$extension";
          }
          if(in_array($extension, self::$not_trusted)) {
            self::$new_name .= self::$suffix;
          }
        } while(in_array(self::$new_name, $existing_files));
      }
    }
  }
}

?>
