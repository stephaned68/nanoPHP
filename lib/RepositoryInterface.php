<?php

namespace framework;

use Exception;

/**
 * Base DAO functions for entities
 * Class BaseRepository
 * @package framework
 */
interface RepositoryInterface
{
  /**
   * Get all entities
   * @return array
   * @throws Exception
   */
  public function getAll(): array;

  /**
   * Get one entity
   * @param $id
   * @return object
   * @throws Exception
   */
  public function getOne($id): ?object;

  /**
   * Filter entities by criteria
   * @param array $filters
   * @return array
   * @throws Exception
   */
  public function findBy(array $filters): array;

  /**
   * Insert an entity
   * @param object $entity
   * @return int
   * @throws Exception
   */
  public function insertOne(object $entity): int;

  /**
   * Update an entity
   * @param object $entity
   * @return int
   * @throws Exception
   */
  public function updateOne(object $entity): int;

  /**
   * Save an entity
   * @param object $entity
   * @return int
   * @throws Exception
   */
  public function save(object $entity): int;

  /**
   * Delete an entity
   * @param object $entity
   * @return int
   * @throws Exception
   */
  public function deleteOne(object $entity): int;
}