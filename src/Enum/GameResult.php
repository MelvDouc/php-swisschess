<?php

namespace MelvDouc\SwissChess\Enum;

use MelvDouc\SwissChess\Interface\Pairing;
use MelvDouc\SwissChess\Interface\Player;

enum GameResult: string
{
  public static function getWinner(Pairing $pairing): ?Player
  {
    $result = self::tryFrom($pairing->getResult());
    return match ($result) {
      self::WhiteWin, self::WhiteWinByForfeit => $pairing->getWhitePlayer(),
      self::BlackWin, self::BlackWinByForfeit => $pairing->getBlackPlayer(),
      default => null
    };
  }

  public static function isDraw(Pairing $pairing): bool
  {
    return $pairing->getResult() === self::Draw->value;
  }

  public static function isWinByForfeit(Pairing $pairing): bool
  {
    $result = self::tryFrom($pairing->getResult());
    return $result === self::WhiteWinByForfeit || $result === self::BlackWinByForfeit;
  }

  case WhiteWin = "1-0";
  case WhiteWinByForfeit = "1-F";
  case BlackWin = "0-1";
  case BlackWinByForfeit = "F-1";
  case Draw = "1/2-1/2";
  case None = "*";
}