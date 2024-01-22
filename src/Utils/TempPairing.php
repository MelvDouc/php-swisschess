<?php

namespace MelvDouc\SwissChess\Utils;

use MelvDouc\SwissChess\Enum\GameResult;
use MelvDouc\SwissChess\Interface\Player;

class TempPairing
{
  public function __construct(
    public readonly Player $whitePlayer,
    public readonly ?Player $blackPlayer,
    public readonly GameResult $result
  ) {
  }
}