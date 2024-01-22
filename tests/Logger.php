<?php

namespace MelvDouc\SwissChessTests;

class Logger
{
  protected static function getLogDir(): string
  {
    return dirname(__DIR__) . "/local/logs";
  }

  public static function log(mixed $data): void
  {
    $message = json_encode($data, JSON_PRETTY_PRINT);
    [$_, $caller] = debug_backtrace(false, 2);
    $fileName = date("Y-m-d\THis");
    file_put_contents(static::getLogDir() . "/$fileName.json", $message);
  }
}