<?php

namespace MelvDouc\SwissChessTests;

use MelvDouc\SwissChess\Enum\GameResult;

require_once dirname(__DIR__) . "/vendor/autoload.php";

use MelvDouc\Obrussa\TestSuite;
use MelvDouc\SwissChess\PairingMaker;

TestSuite::test("First round pairings should be rating-based.", function (TestSuite $testSuite) {
  $p1 = new TestPlayer("p1", 2800);
  $p2 = new TestPlayer("p2", 2700);
  $p3 = new TestPlayer("p3", 1600);
  $p4 = new TestPlayer("p4", 1500);
  $players = [$p1, $p2, $p3, $p4];
  $pairingMaker = new PairingMaker($players);
  $pairings = $pairingMaker->getNextPairings(1);
  $testSuite->assertCount($pairings, 2);
  $testSuite->assertEquals($p1, $pairings[0]->whitePlayer);
  $testSuite->assertEquals($p3, $pairings[0]->blackPlayer);
  $testSuite->assertEquals($p4, $pairings[1]->whitePlayer);
  $testSuite->assertEquals($p2, $pairings[1]->blackPlayer);
});

TestSuite::test("Subsequent round pairings should be point-based.", function (TestSuite $testSuite) {
  $p1 = new TestPlayer("p1");
  $p2 = new TestPlayer("p2");
  $p3 = new TestPlayer("p3");
  $p4 = new TestPlayer("p4");
  $players = [$p1, $p2, $p3, $p4];
  new TestPairing(1, $p1, $p3, GameResult::WhiteWin->value);
  new TestPairing(1, $p4, $p2, GameResult::Draw->value);
  $pairingMaker = new PairingMaker($players);
  $pairings = $pairingMaker->getNextPairings(2);
  $testSuite->assertCount($pairings, 2);
});

TestSuite::test("Everyone should be paired up.", function (TestSuite $testSuite) {
  $numberOfPlayers = 10;
  $numberOfRounds = 4;
  $players = TestUtils::playRandomTournament($numberOfPlayers, $numberOfRounds)->getPlayers();

  foreach ($players as $player) {
    $testSuite->assertCount($player->getHistory(), $numberOfRounds);
  }
});

TestSuite::test("Standings should be sorted.", function (TestSuite $testSuite) {
  $numberOfPlayers = 15;
  $numberOfRounds = 5;
  $pairingMaker = TestUtils::playRandomTournament($numberOfPlayers, $numberOfRounds);
  $standings = $pairingMaker->getStandings();

  for ($i = 1; $i < count($standings); $i++) {
    $standing = $standings[$i];
    $prev = $standings[$i - 1];
    $testSuite->assertLessThanOrEqualTo(bigger: $prev->points, smaller: $standing->points);
  }
});

TestSuite::test("A tournament should be able to handle many players.", function (TestSuite $testSuite) {
  $numberOfPlayers = 120;
  $numberOfRounds = 11;
  try {
    TestUtils::playRandomTournament($numberOfPlayers, $numberOfRounds);
    $testSuite->assert(true);
  } catch (\Throwable $e) {
    $testSuite->assertFalse(true);
  }
});

TestSuite::test("A tournament should be able to handle many rounds.", function (TestSuite $testSuite) {
  $numberOfPlayers = 49;
  $numberOfRounds = 15;
  try {
    $pairingMaker = TestUtils::playRandomTournament($numberOfPlayers, $numberOfRounds);
    $players = $pairingMaker->getPlayers();
    $testSuite->expect($players[0]->getHistory())->toHaveCount($numberOfRounds);
    $testSuite->expect($players[$numberOfPlayers - 1]->getHistory())->toHaveCount($numberOfRounds);
    // echo json_encode(TestUtils::formatStandings($pairingMaker->getStandings()), JSON_PRETTY_PRINT) . "\n";
  } catch (\Throwable $e) {
    $testSuite->assertFalse(true, $e->getMessage());
  }
});

TestSuite::run();