<?php

namespace MelvDouc\SwissChessTests;

use MelvDouc\SwissChess\Enum\GameResult;
use MelvDouc\SwissChess\PairingMaker;
use MelvDouc\SwissChess\PlayerResult;
use MelvDouc\SwissChess\Utils\TempPairing;

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

    /** @var TestPairing[][] */
    $rounds = [];
    $roundNumber = 1;

    while (count($rounds) < $numberOfRounds) {
      $pairingMaker = new PairingMaker($players, $rounds);
      $pairings = $pairingMaker->getNextPairings($roundNumber);
      $round = array_map(array: $pairings, callback: function (TempPairing $p) use ($roundNumber) {
        $result = $p->blackPlayer ? self::getRandomResult() : $p->result;
        return new TestPairing($roundNumber, $p->whitePlayer, $p->blackPlayer, $result->value);
      });
      $rounds[] = $round;
      $roundNumber++;
    }

    $pairingMaker = new PairingMaker($players, $rounds);
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