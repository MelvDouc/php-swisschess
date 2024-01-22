<?php

namespace MelvDouc\SwissChess\Utils;

/**
 * @template T
 */
class Map
{
  /**
   * @var array<int|string, T>
   */
  private $entries = [];

  /**
   * @param int|string $key
   * @return bool
   */
  public function has(int|string $key): bool
  {
    return array_key_exists($key, $this->entries);
  }

  /**
   * @param int|string $key
   * @return T
   */
  public function get(int|string $key)
  {
    return $this->entries[$key] ?? null;
  }

  /**
   * @param int|string $key
   * @param T $value
   * @return static
   */
  public function set(int|string $key, mixed $value): static
  {
    $this->entries[$key] = $value;
    return $this;
  }

  /**
   * @param int|string $key
   * @return bool
   */
  public function delete(int|string $key): bool
  {
    $hasKey = $this->has($key);
    if ($hasKey)
      unset($this->entries[$key]);
    return $hasKey;
  }

  /**
   * @return void
   */
  public function clear(): void
  {
    $this->entries = [];
  }

  /**
   * @return (int|string)[]
   */
  public function getKeys(): array
  {
    return array_keys($this->entries);
  }

  /**
   * @return T[]
   */
  public function getValues(): array
  {
    return array_values($this->entries);
  }

  /**
   * @return array `[int|string, T][]`
   */
  public function getEntries(): array
  {
    $entries = [];
    foreach ($this->entries as $key => $value)
      $entries[] = [$key, $value];
    return $entries;
  }
}