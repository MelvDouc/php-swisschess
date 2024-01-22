<?php

namespace MelvDouc\SwissChess;

use MelvDouc\SwissChess\Enum\Color;
use MelvDouc\SwissChess\Enum\GameResult;
use MelvDouc\SwissChess\Interface\Pairing;
use MelvDouc\SwissChess\Interface\Player;

readonly class PlayerResult
{
  public static function from(Pairing $pairing, Player $player, array $indices): static
  {
    $opponent = $pairing->getWhitePlayer()?->getId() === $player->getId()
      ? $pairing->getBlackPlayer()
      : $pairing->getWhitePlayer();
    $opponentPosition = $opponent ? ($indices[$opponent->getId()] + 1) : 0;
    return new static(
      $player,
      $opponent,
      $opponentPosition,
      Color::getPlayerColor($pairing, $player),
      static::getResultAbbreviation($pairing, $player)
    );
  }

  public static function getResultAbbreviation(Pairing $pairing, Player $player): string
  {
    if (GameResult::getWinner($pairing)?->getId() === $player->getId())
      return "+";
    if (GameResult::isDraw($pairing))
      return "=";
    return "-";
  }

  public Player $player;
  public ?Player $opponent;
  public int $opponentPosition;
  public Color $color;
  public string $ownResult;

  public function __construct(Player $player, ?Player $opponent, int $opponentPosition, Color $color, string $ownResult)
  {
    $this->player = $player;
    $this->opponent = $opponent;
    $this->opponentPosition = $opponentPosition;
    $this->color = $color;
    $this->ownResult = $ownResult;
  }
}