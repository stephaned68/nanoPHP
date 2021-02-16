<?php


namespace framework;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Utility functions
 *
 * Class Tools
 * @package framework
 */
class Tools
{

  /**
   * Pluralize a string
   *
   * @param $singular
   * @return string
   */
  public static function pluralize($singular)
  {
    $last_letter = $singular[strlen($singular) - 1];
    switch ($last_letter) {
      case 'y':
        return substr($singular, 0, -1) . "ies";
      case 's':
        return $singular . "es";
      default:
        return $singular . "s";
    }
  }

  /**
   * Pascalize a string (my_var > MyVar)
   *
   * @param string $str
   * @return string|string[]|null
   */
  public static function pascalize($str)
  {
    $pattern = "#(\_|-| )?([a-zA-Z0-9])+#";
    return preg_replace_callback(
      $pattern,
      function ($matches) {
        $matches[0] = str_replace($matches[1], "", $matches[0]);
        $matches[0] = strtoupper(substr($matches[0], 0, 1))
          . strtolower(substr($matches[0], 1));

        return $matches[0];
      },
      $str
    );
  }

  /**
   * Camelize a string (my_var > myVar)
   *
   * @param string $str
   * @return string
   */
  public static function camelize($str)
  {
    $temp = self::pascalize($str);
    return strtolower(substr($temp, 0, 1))
      . substr($temp, 1);
  }

  /**
   * Convert a string to snake case (myVar | MyVar > my_var)
   *
   * @param $str
   * @return string
   */
  public static function snakeCase($str)
  {
    $temp = str_split($str);
    $result = "";
    foreach ($temp as $char) {
      if (strtoupper($char) == $char && $result != "") {
        $result .= "_";
      }
      $result .= strtolower($char);
    }
    return $result;
  }

  /**
   * Add a flash message to the session
   *
   * @param string|array $message
   * @param null $type
   */
  public static function setFlash($message, $type = null)
  {
    $type = $type ?? "flash";

    if (array_key_exists($type, $_SESSION)) {
      $messages = $_SESSION[$type];
    } else {
      $messages = [];
    }

    if (is_array($message)) {
      if (is_array($messages) && count($messages) > 0) {
        array_merge($messages, $message);
      } else {
        $messages = $message;
      }
    } else {
      array_push($messages, $message);
    }
    $_SESSION[$type] = $messages;
  }

  /**
   * Get flash messages for a type
   *
   * @param null $type
   * @return mixed|string
   */
  public static function getFlash($type = null)
  {
    $type = $type ?? "flash";
    $messages = $_SESSION[$type] ?? [];
    unset($_SESSION[$type]);
    return $messages;
  }

  /**
   * Return an attribute value from an object
   *
   * @param object $o
   * @param string $attribute
   * @return mixed|null
   */
  public static function getProperty(object $o, string $attribute)
  {
    $value = null;
    $getter = "get" . ucfirst($attribute);
    if (method_exists($o, $getter)) {
      $value = $o->$getter();
    }
    return $value;
  }

  /**
   * Set an object's attribute value
   *
   * @param object $o
   * @param string $attribute
   * @param $value
   */
  public static function setProperty(object $o, string $attribute, $value) : void
  {
    $setter = "set" . ucfirst($attribute);
    if (method_exists($o, $setter)) {
      $o->$setter($value);
    }
  }

  /**
   * Auto-mapping from source to target object
   *
   * Can take an associative array of {source property} => {target property}
   *
   * @param object $source
   * @param object $target
   * @param array $mapping
   * @throws ReflectionException
   */
  public static function map(object $source, object $target, array $mapping = []) : void
  {
    $mappedTo = array_flip($mapping);
    $reflect = new ReflectionClass($target);
    $props   = $reflect->getProperties(
      ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE
    );
    foreach ($props as $prop) {
      $propName = $prop->getName();
      $propSource = $mappedTo[$propName] ?? $propName;
      $propValue = self::getProperty($source, $propSource);
      if ($propValue == null)
        $propValue = $source->$propSource;
      if ($propValue != null) {
        if ($prop->isPublic()) {
          $target->$propName = $propValue;
        }
        else {
          self::setProperty($target, $propName, $propValue);
        }
      }
    }
  }

  /**
   * Extract a key => value array
   *
   * from an array of associative arrays or objects
   *
   * @param array $list
   * @param string $valueField
   * @param string $labelField
   * @return array
   */
  public static function select(array $list, string $valueField, string $labelField) : array
  {
    $select = [];
    foreach ($list as $item) {
      $key = null;
      $value = null;
      if (is_array($item)) {
        $key = $item[$valueField];
        $value = $item[$labelField];
      }
      if (is_object($item)) {
        $key = self::getProperty($item, $valueField);
        $value = self::getProperty($item, $labelField);
      }
      if ($key != null && $value != null) {
        $select[$key] = $value;
      }
    }

    return $select;
  }

}