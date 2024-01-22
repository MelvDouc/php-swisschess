<?php

namespace MelvDouc\SwissChess\Enum;

use MelvDouc\SwissChess\Interface\Pairing;
use MelvDouc\SwissChess\Interface\Player;

enum Color
{
  public static function getPlayerColor(Pairing $pairing, Player $player): self
  {
    return match ($player->getId()) {
      $pairing->getWhitePlayer()?->getId() => self::White,
      $pairing->getBlackPlayer()?->getId() => self::Black,
      default => self::None
    };
  }

  case White;
  case Black;
  case None;
}