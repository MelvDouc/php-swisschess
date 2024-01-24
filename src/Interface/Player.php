<?php

namespace MelvDouc\SwissChess\Interface;

interface Player
{
  /**
   * @return int
   */
  public function getId();
  /**
   * @return int
   */
  public function getRating();
}