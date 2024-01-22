<?php

use MelvDouc\SwissChess\Interface\Player as IPlayer;
use MelvDouc\SwissChess\Interface\Pairing as IPairing;

class TestPlayer implements IPlayer
{
  private static $idGenerator = 1;

  private readonly int $id;
  private readonly string $name;
  private array $history = [];

  public function __construct(string $name)
  {
    $this->id = self::$idGenerator++;
    $this->name = $name;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getHistory()
  {
    return $this->history;
  }

  public function addToHistory(IPairing $pairing)
  {
    $this->history[] = $pairing;
  }

  public function getRating()
  {
    return 1199;
  }
}