<?php


namespace framework;

/**
 * Interface MigrationInterface
 * @package framework
 */
interface MigrationInterface
{
  /**
   * @return string
   */
  public function getDescription() : string;

  /**
   * @return bool|mixed
   */
  public function execute();
}