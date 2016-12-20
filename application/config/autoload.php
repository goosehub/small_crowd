<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// CodeIgniter Features
$autoload['packages'] = array();
$autoload['libraries'] = array('session', 'form_validation');
$autoload['drivers'] = array();
$autoload['helper'] = array('url', 'form');
$autoload['config'] = array();
$autoload['language'] = array();
$autoload['model'] = array();

// Set timezone
date_default_timezone_set('America/New_York');

// 
// General Purpose Functions
// 

// Return if this is dev
function is_dev() {
    if ($_SERVER['HTTP_HOST'] === 'localhost') {
        return true;
    }
    return false;
}

// Auth Token
function auth_token() {
    $auth = file_get_contents('auth.php');
    return $auth;
}

// Function to format names
// http://php.net/manual/en/function.ucwords.php (First comment)
function ucname($string) {
    $string =ucwords(strtolower($string));
    foreach (array('-', '\'') as $delimiter) {
      if (strpos($string, $delimiter) !== false) {
        $string =implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
      }
    }
    return $string;
}

// For human readable spans of time
// http://stackoverflow.com/questions/2915864/php-how-to-find-the-time-elapsed-since-a-date-time
function get_time_ago($time_stamp) {
    $time_difference = strtotime('now') - $time_stamp;
    if ($time_difference >= 60 * 60 * 24 * 365.242199) {
        return get_time_ago_string($time_stamp, 60 * 60 * 24 * 365.242199, 'year');
    }
    else if ($time_difference >= 60 * 60 * 24 * 30.4368499) {
        return get_time_ago_string($time_stamp, 60 * 60 * 24 * 30.4368499, 'month');
    }
    else if ($time_difference >= 60 * 60 * 24 * 7) {
        return get_time_ago_string($time_stamp, 60 * 60 * 24 * 7, 'week');
    }
    else if ($time_difference >= 60 * 60 * 24) {
        return get_time_ago_string($time_stamp, 60 * 60 * 24, 'day');
    }
    else if ($time_difference >= 60 * 60) {
        return get_time_ago_string($time_stamp, 60 * 60, 'hour');
    }
    else {
        return get_time_ago_string($time_stamp, 60, 'minute');
    }
}
function get_time_ago_string($time_stamp, $divisor, $time_unit) {
    $time_difference = strtotime("now") - $time_stamp;
    $time_units      = floor($time_difference / $divisor);
    settype($time_units, 'string');
    if ($time_units === '0') {
        return 'less than 1 ' . $time_unit . ' ago';
    }
    else if ($time_units === '1') {
        return '1 ' . $time_unit . ' ago';
    }
    else {
        return $time_units . ' ' . $time_unit . 's ago';
    }
}

// Flash Message
// http://www.phpdevtips.com/2013/05/simple-session-based-flash-messages/
function flash( $name = '', $message = '', $class = 'success' ) {
    // We can only do something if the name isn't empty
    if ( !empty( $name ) ) {
        // No message, create it
        if ( !empty( $message ) && empty( $_SESSION[$name] ) ) {
            if ( !empty( $_SESSION[$name] ) ) {
                unset( $_SESSION[$name] );
            }
            if ( !empty( $_SESSION[$name.'_class'] ) ) {
                unset( $_SESSION[$name.'_class'] );
            }
            $_SESSION[$name] = $message;
            $_SESSION[$name.'_class'] = $class;
        }
        // Message exists, display it
        else if ( !empty( $_SESSION[$name] ) && empty( $message ) ) {
            $class = !empty( $_SESSION[$name.'_class'] ) ? $_SESSION[$name.'_class'] : 'success';
            echo '<div class="alert alert-' . $class . '" role="alert">' . $_SESSION[$name] . '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name.'_class']);
        }
    }
}

// Create hash_equals function if not exists (for token authentication)
function createHashEquals() {
    if (!function_exists('hash_equals')) {
      function hash_equals($str1, $str2) {
        if (strlen($str1) != strlen($str2)) {
          return false;
        } else {
          $res = $str1 ^ $str2;
          $ret = 0;
          for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
          return !$ret;
        }
      }
    }
}