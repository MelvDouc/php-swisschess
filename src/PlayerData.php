<?php

namespace MelvDouc\SwissChess;

use MelvDouc\SwissChess\Enum\Color;
use MelvDouc\SwissChess\Enum\GameResult;
use MelvDouc\SwissChess\Interface\Pairing;
use MelvDouc\SwissChess\Interface\Player;

// TODO: Elo performance
class PlayerData
{
  public static function compareEncounter(PlayerData $data1, PlayerData $data2): int
  {
    $id2 = $data2->player->getId();

    foreach ($data1->history as $pairing) {
      $whitePlayer = $pairing->getWhitePlayer();
      $blackPlayer = $pairing->getBlackPlayer();
      if ($whitePlayer->getId() !== $id2 && $blackPlayer?->getId() !== $id2)
        continue;
      if (GameResult::getWinner($pairing)?->getId() === $id2)
        return -1;
      return GameResult::isDraw($pairing) ? 0 : 1;
    }

    return 0;
  }

  public static function getIdealColor(PlayerData $data1, PlayerData $data2): Color
  {
    if ($data1->hasPlayed($data2->player))
      return Color::None;

    if ($data1->mustAlternate) {
      if ($data2->mustAlternate && $data2->previousColor === $data1->previousColor)
        return Color::None;
      return $data1->previousColor === Color::Black
        ? Color::White
        : Color::Black;
    }

    if (
      $data2->mustAlternate && $data2->previousColor === Color::White
      || $data1->numberOfWhiteGames < $data2->numberOfWhiteGames
      || $data1->points < $data2->points
    )
      return Color::White;

    return Color::Black;
  }

  public readonly Player $player;
  /**
   * @var Pairing[]
   */
  public readonly array $history;
  public readonly float $points;
  public readonly float $cumulativeScore;
  public readonly int $numberOfWhiteGames;
  public readonly int $numberOfWins;
  public readonly Color $previousColor;
  public readonly bool $mustAlternate;
  public readonly bool $canBeBye;
  /**
   * @var int[]
   */
  private readonly array $opponentIds;
  private float $opponentPoints = 0.0;
  private bool $isFrozen = false;

  public function __construct(Player $player, array $history, float $pointsPerWin, float $pointsPerDraw)
  {
    $this->player = $player;
    $this->history = $history;
    $data = $this->getData($pointsPerWin, $pointsPerDraw);
    $numberOfGamesPlayed = count($this->history);
    $numberOfBlackGames = $numberOfGamesPlayed - $data["numberOfWhiteGames"];

    $previousColor = $numberOfGamesPlayed > 0
      ? Color::getPlayerColor($this->history[$numberOfGamesPlayed - 1], $player)
      : Color::None;
    $antePreviousColor = $numberOfGamesPlayed > 1
      ? Color::getPlayerColor($this->history[$numberOfGamesPlayed - 2], $player)
      : Color::None;

    $this->opponentIds = $data["opponentIds"];
    $this->points = $data["points"];
    $this->cumulativeScore = $data["cumulativeScore"];
    $this->numberOfWhiteGames = $data["numberOfWhiteGames"];
    $this->numberOfWins = $data["numberOfWins"];
    $this->previousColor = $previousColor;
    $this->mustAlternate = $previousColor !== Color::None && $previousColor === $antePreviousColor
      || abs($this->numberOfWhiteGames - $numberOfBlackGames) >= 2;
    $this->canBeBye = !$data["hasBeenBye"] && !$data["wonByForfeit"];
  }

  public function getOpponentPoints(): float
  {
    return $this->opponentPoints;
  }

  public function setOpponentPoints(float $opponentPoints): void
  {
    if ($this->isFrozen)
      throw new \Exception("Opponent points are already set.");

    $this->opponentPoints = $opponentPoints;
    $this->isFrozen = true;
  }

  public function hasPlayed(Player $opponent): bool
  {
    return in_array(haystack: $this->opponentIds, needle: $opponent->getId());
  }

  public function getOpponentIds(): array
  {
    return $this->opponentIds;
  }

  public function compare(PlayerData $opponentData): int
  {
    if ($pointDiff = $this->points - $opponentData->points)
      return $pointDiff < 0 ? -1 : 1;
    if ($buchholz = $this->getOpponentPoints() - $opponentData->getOpponentPoints())
      return $buchholz < 0 ? -1 : 1;
    if ($scoreDiff = $this->cumulativeScore - $opponentData->cumulativeScore)
      return $scoreDiff < 0 ? -1 : 1;
    if ($encounterDiff = static::compareEncounter($this, $opponentData))
      return $encounterDiff;
    return $this->numberOfWins - $opponentData->numberOfWins;
  }

  private function getData(float $pointsPerWin, float $pointsPerDraw)
  {
    $data = [
      "opponentIds" => [],
      "points" => 0.0,
      "cumulativeScore" => 0.0,
      "numberOfWhiteGames" => 0,
      "numberOfWins" => 0,
      "wonByForfeit" => false,
      "hasBeenBye" => false
    ];

    foreach ($this->history as $pairing) {
      $whitePlayer = $pairing->getWhitePlayer();
      $blackPlayer = $pairing->getBlackPlayer();
      $isWhite = $whitePlayer->getId() === $this->player->getId();
      $opponent = $isWhite ? $blackPlayer : $whitePlayer;

      if ($isWhite)
        $data["numberOfWhiteGames"]++;

      if ($opponent)
        $data["opponentIds"][] = $opponent->getId();
      else
        $data["hasBeenBye"] = true;

      if (GameResult::getWinner($pairing)?->getId() === $this->player->getId()) {
        $data["points"] += $pointsPerWin;
        $data["numberOfWins"]++;
        if (GameResult::isWinByForfeit($pairing))
          $data["wonByForfeit"] = true;
      } else if (GameResult::isDraw($pairing)) {
        $data["points"] += $pointsPerDraw;
      }

      $data["cumulativeScore"] += $data["points"];
    }

    return $data;
  }
}