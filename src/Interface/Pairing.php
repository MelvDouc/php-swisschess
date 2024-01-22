<?php

namespace MelvDouc\SwissChess\Interface;

interface Pairing
{
  /**
   * @return int
   */
  public function getRoundNumber();
  /**
   * @return Player
   */
  public function getWhitePlayer();
  /**
   * @return Player|null
   */
  public function getBlackPlayer();
  /**
   * @return string
   */
  public function getResult();
}