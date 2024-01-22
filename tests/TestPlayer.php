<?php

namespace MelvDouc\SwissChessTests;

use MelvDouc\SwissChess\Interface\Player as IPlayer;
use MelvDouc\SwissChess\Interface\Pairing as IPairing;

class TestPlayer implements IPlayer
{
  private static $idGenerator = 1;

  private readonly int $id;
  private readonly string $name;
  private readonly int $rating;
  private array $history = [];

  public function __construct(string $name, int $rating = 1199)
  {
    $this->id = self::$idGenerator++;
    $this->name = $name;
    $this->rating = $rating;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getRating(): int
  {
    return $this->rating;
  }

  public function getHistory()
  {
    return $this->history;
  }

  public function addToHistory(IPairing $pairing)
  {
    $this->history[] = $pairing;
  }
}