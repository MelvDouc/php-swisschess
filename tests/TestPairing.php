<?php
namespace MelvDouc\SwissChessTests;

use MelvDouc\SwissChess\Interface\Pairing as IPairing;

class TestPairing implements IPairing
{
  public function __construct(
    private readonly int $roundNumber,
    private readonly TestPlayer $whitePlayer,
    private readonly ?TestPlayer $blackPlayer,
    private string $result,
  ) {
    $whitePlayer->addToHistory($this);
    $blackPlayer?->addToHistory($this);
  }

  public function getRoundNumber(): int
  {
    return $this->roundNumber;
  }

  public function getWhitePlayer(): TestPlayer
  {
    return $this->whitePlayer;
  }

  public function getBlackPlayer(): ?TestPlayer
  {
    return $this->blackPlayer;
  }

  public function getResult(): string
  {
    return $this->result;
  }

  public function toJSON()
  {
    return [
      "roundNumber" => $this->roundNumber,
      "white" => $this->whitePlayer->getName(),
      "black" => $this->blackPlayer?->getName() ?? "-",
      "result" => $this->result
    ];
  }
}