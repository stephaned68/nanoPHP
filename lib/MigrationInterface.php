<?php


namespace framework;


interface MigrationInterface
{
  public function getDescription();
  public function execute();
}