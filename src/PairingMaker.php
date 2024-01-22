<?php

namespace MelvDouc\SwissChess;

use MelvDouc\SwissChess\Enum\Color;
use MelvDouc\SwissChess\Interface\Pairing;
use MelvDouc\SwissChess\Interface\Player;
use MelvDouc\SwissChess\Utils\Map;

class PairingMaker
{
  /**
   * @var Player[]
   */
  private readonly array $players;
  private readonly float $pointsPerWin;
  private readonly float $pointsPerDraw;

  /**
   * @param Player[] $players
   * @param float $pointsPerWin
   * @param float $pointsPerDraw
   */
  public function __construct(array $players, float $pointsPerWin = 1, float $pointsPerDraw = 0.5)
  {
    $this->players = $players;
    $this->pointsPerWin = $pointsPerWin;
    $this->pointsPerDraw = $pointsPerDraw;
  }

  /**
   * @return Standing[]
   */
  public function getStandings()
  {
    $dataMap = $this->getPlayerDataMap();
    $players = $this->players;
    usort($players, function (Player $a, Player $b) use ($dataMap) {
      return $dataMap->get($b->getId())->compare($dataMap->get($a->getId()));
    });
    $indices = array_reduce(array_keys($players), function (array $acc, int $index) use ($players) {
      $acc[$players[$index]->getId()] = $index;
      return $acc;
    }, []);
    return array_map(
      array: $players,
      callback: function (Player $player) use ($dataMap, $indices) {
        $data = $dataMap->get($player->getId());
        return new Standing(
          player: $player,
          points: $data->points,
          opponentPoints: $data->getOpponentPoints(),
          numberOfWins: $data->numberOfWins,
          numberOfWhiteGames: $data->numberOfWhiteGames,
          results: array_map(
            array: $data->history,
            callback: fn(Pairing $pairing) => PlayerResult::from($pairing, $player, $indices)
          )
        );
      }
    );
  }

  /**
   * @return Player[][] - An array of the type `[whitePlayer: Player, blackPlayer: Player|null][]`.
   */
  public function getNextPairings(int $roundNumber): array
  {
    return ($roundNumber === 1)
      ? $this->getFirstRoundPairings()
      : $this->getSubsequentRoundPairings();
  }

  /**
   * @return Map<PlayerData>
   */
  public function getPlayerDataMap(): Map
  {
    /** @var Map<PlayerData> */
    $dataMap = new Map();

    foreach ($this->players as $player) {
      $data = new PlayerData($player, $this->pointsPerWin, $this->pointsPerDraw);
      $dataMap->set($player->getId(), $data);
    }

    foreach ($dataMap->getEntries() as $entry) {
      [$id, $data] = $entry;
      $opponentPoints = 0;
      foreach ($data->getOpponentIds() as $opponentId)
        $opponentPoints += $dataMap->get($opponentId)->points;
      $dataMap->get($id)->setOpponentPoints($opponentPoints);
    }

    return $dataMap;
  }

  private function hasBye(): bool
  {
    return count($this->players) % 2 === 1;
  }

  private function getFirstRoundPairings(): array
  {
    $players = $this->players;
    usort($players, fn(Player $a, Player $b) => $b->getRating() - $a->getRating());
    $bye = $this->hasBye() ? array_pop($players) : null;
    $halfLength = count($players) / 2;
    /** @var Player[][] */
    $pairings = [];

    for ($i = 0; $i < $halfLength; $i++) {
      $player1 = $players[$i];
      $player2 = $players[$i + $halfLength];
      $pairings[] = ($i % 2 === 0)
        ? [$player1, $player2]
        : [$player2, $player1];
    }

    if ($bye)
      $pairings[] = [$bye, null];

    return $pairings;
  }

  private function getSubsequentRoundPairings(): array
  {
    $dataMap = $this->getPlayerDataMap();
    $players = $this->players;
    usort($players, function (Player $a, Player $b) use ($dataMap) {
      return $dataMap->get($b->getId())->compare($dataMap->get($a->getId()));
    });
    $bye = null;

    if ($this->hasBye()) {
      for ($i = count($players) - 1; $i >= 0; $i--) {
        $id = $players[$i]->getId();
        if ($dataMap->get($id)->canBeBye) {
          $bye = array_splice($players, $i, 1)[0];
          break;
        }
      }
    }

    /** @var Map<Player[]> */
    $pairingRecord = new Map();
    $this->tryPairings($players, $dataMap, $pairingRecord);
    $pairings = $pairingRecord->getValues();

    if ($bye)
      $pairings[] = [$bye, null];

    return $pairings;
  }

  /**
   * @param Player[] $players
   * @param Map<PlayerData> $dataMap
   * @param Map<Player[]> $pairingMap
   */
  private function tryPairings(array $players, Map $dataMap, Map $pairingMap)
  {
    if (!$players)
      return true;

    $topSeed = $players[0];
    $topSeedId = $topSeed->getId();

    for ($i = 1; $i < count($players); $i++) {
      $opponent = $players[$i];
      $opponentId = $opponent->getId();
      $color = PlayerData::getIdealColor($dataMap->get($topSeedId), $dataMap->get($opponentId));

      if ($color === Color::None)
        continue;

      $pairing = ($color === Color::White)
        ? [$topSeed, $opponent]
        : [$opponent, $topSeed];
      $key = ((string) $topSeedId) . "-" . ((string) $opponentId);
      $pairingMap->set($key, $pairing);
      $otherPlayers = array_slice($players, 1);
      array_splice($otherPlayers, $i - 1, 1);

      if ($this->tryPairings($otherPlayers, $dataMap, $pairingMap))
        return true;

      $pairingMap->delete($key);
    }

    return false;
  }
}