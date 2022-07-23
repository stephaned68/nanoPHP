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
   * @return mixed
   */
  public function execute(): mixed;
}