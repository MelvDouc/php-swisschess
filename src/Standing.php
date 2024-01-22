<?php

namespace MelvDouc\SwissChess;

use MelvDouc\SwissChess\Interface\Player;

class Standing
{
  public function __construct(
    public readonly Player $player,
    public readonly float $points,
    public readonly float $opponentPoints,
    public readonly int $numberOfWins,
    public readonly int $numberOfWhiteGames,
    /**
     * @var PlayerResult[]
     */
    public readonly array $results
  ) {
  }
}