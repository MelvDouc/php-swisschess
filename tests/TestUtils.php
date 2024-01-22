<?php

namespace MelvDouc\SwissChessTests;

use MelvDouc\SwissChess\Enum\GameResult;
use MelvDouc\SwissChess\PairingMaker;
use MelvDouc\SwissChess\PlayerResult;

final class TestUtils
{
  public static function getRandomResult(): GameResult
  {
    return match (random_int(1, 3)) {
      1 => GameResult::WhiteWin,
      2 => GameResult::Draw,
      3 => GameResult::BlackWin
    };
  }

  public static function playRandomTournament(int $numberOfPlayers, int $numberOfRounds): PairingMaker
  {
    /** @var TestPlayer[] */
    $players = [];

    for ($i = 1; $i <= $numberOfPlayers; $i++)
      $players[] = new TestPlayer("player-$i");

    $pairingMaker = new PairingMaker($players);

    for ($i = 1; $i <= $numberOfRounds; $i++) {
      $pairings = $pairingMaker->getNextPairings($i);
      foreach ($pairings as $p) {
        new TestPairing($i, $p->whitePlayer, $p->blackPlayer, $p->result->value);
      }
    }

    return $pairingMaker;
  }

  public static function formatStandings(array $standings)
  {
    return array_map(array: array_keys($standings), callback: function (int $i) use ($standings) {
      $standing = $standings[$i];
      return [
        "position" => $i + 1,
        "playerId" => $standing->player->getId(),
        "points" => $standing->points,
        "opponentPoints" => $standing->opponentPoints,
        "results" => array_map(array: $standing->results, callback: function (PlayerResult $playerResult) {
          $color = $playerResult->color->name[0];
          return (string) $playerResult->opponentPosition . $color . $playerResult->ownResult;
        })
      ];
    });
  }
}