<?php

namespace framework;

abstract class BaseDTO
{

  /**
   * BaseDTO constructor.
   * @param object $source Source object to import data from
   */
  public function __construct(object $source)
  {
    $props = get_object_vars($this);
    foreach ($props as $prop) {
      $this->$prop = Tools::getProperty($source, $prop);
    }
  }

  abstract public function convert() : void;

}