<?php
use MelvDouc\SwissChess\Enum\GameResult;
use MelvDouc\SwissChess\PairingMaker;
use MelvDouc\SwissChess\PlayerResult;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/Logger.php";
require_once __DIR__ . "/TestPairing.php";

class PairingMakerTest extends TestCase
{
  public function testFirstRoundPairings()
  {
    $p1 = new TestPlayer("p1");
    $p2 = new TestPlayer("p2");
    $p3 = new TestPlayer("p3");
    $p4 = new TestPlayer("p4");
    $players = [$p1, $p2, $p3, $p4];
    $pairingMaker = new PairingMaker($players);
    $pairings = $pairingMaker->getNextPairings(1);
    $this->assertCount(2, $pairings);
    $this->assertEquals($p1, $pairings[0][0]);
    $this->assertEquals($p3, $pairings[0][1]);
    $this->assertEquals($p4, $pairings[1][0]);
    $this->assertEquals($p2, $pairings[1][1]);
  }

  public function testSecondRoundPairings()
  {
    $p1 = new TestPlayer("p1");
    $p2 = new TestPlayer("p2");
    $p3 = new TestPlayer("p3");
    $p4 = new TestPlayer("p4");
    $players = [$p1, $p2, $p3, $p4];
    new TestPairing(1, $p1, $p3, GameResult::WhiteWin->value);
    new TestPairing(1, $p4, $p2, GameResult::Draw->value);
    $pairingMaker = new PairingMaker($players);
    $pairings = $pairingMaker->getNextPairings(2);
    $this->assertCount(2, $pairings);
  }

  public function testEveryoneIsPaired()
  {
    $numberOfPlayers = 10;
    $numberOfRounds = 4;
    [$players] = $this->playRandomTournament($numberOfPlayers, $numberOfRounds);

    foreach ($players as $player)
      $this->assertCount($numberOfRounds, $player->getHistory());
  }

  public function testStandingOrder()
  {
    $numberOfPlayers = 15;
    $numberOfRounds = 5;
    [$players, $pairingMaker] = $this->playRandomTournament($numberOfPlayers, $numberOfRounds);
    $standings = $pairingMaker->getStandings();

    for ($i = 1; $i < count($standings); $i++) {
      $standing = $standings[$i];
      $prev = $standings[$i - 1];
      $this->assertLessThanOrEqual($prev->points, $standing->points);
    }
  }

  public function testManyPlayers()
  {
    $numberOfPlayers = 120;
    $numberOfRounds = 6;
    try {
      $this->playRandomTournament($numberOfPlayers, $numberOfRounds);
      $this->assertTrue(true);
    } catch (\Throwable $e) {
      $this->assertFalse(true);
    }
  }

  public function testManyRounds()
  {
    $numberOfPlayers = 51;
    $numberOfRounds = 15;
    try {
      [$players, $pairingMaker] = $this->playRandomTournament($numberOfPlayers, $numberOfRounds);
      $this->assertTrue(true);
      $standings = $pairingMaker->getStandings();
      Logger::log($this->formatStandings($standings));
    } catch (\Throwable $e) {
      $this->assertFalse(true, $e->getMessage());
    }
  }

  private function formatStandings(array $standings)
  {
    return array_map(array: array_keys($standings), callback: function (int $i) use ($standings) {
      $standing = $standings[$i];
      return [
        "position" => $i + 1,
        "playerId" => $standing->player->getId(),
        "points" => $standing->points,
        "opponentPoints" => $standing->opponentPoints,
        "results" => array_map(array: $standing->results, callback: function (PlayerResult $playerResult) {
          return (string) $playerResult->opponentPosition . $playerResult->color->value . $playerResult->ownResult;
        })
      ];
    });
  }

  private function getRandomResult(): GameResult
  {
    return match (random_int(1, 3)) {
      1 => GameResult::WhiteWin,
      2 => GameResult::Draw,
      3 => GameResult::BlackWin
    };
  }

  private function playRandomTournament(int $numberOfPlayers, int $numberOfRounds): array
  {
    /** @var TestPlayer[] */
    $players = [];

    for ($i = 1; $i <= $numberOfPlayers; $i++)
      $players[] = new TestPlayer("player-$i");

    $pairingMaker = new PairingMaker($players);

    for ($i = 1; $i <= $numberOfRounds; $i++) {
      $pairings = $pairingMaker->getNextPairings($i);
      foreach ($pairings as $p) {
        [$whitePlayer, $blackPlayer] = $p;
        $result = $blackPlayer
          ? $this->getRandomResult()
          : GameResult::WhiteWin;
        new TestPairing($i, $whitePlayer, $blackPlayer, $result->value);
      }
    }

    return [$players, $pairingMaker];
  }
}