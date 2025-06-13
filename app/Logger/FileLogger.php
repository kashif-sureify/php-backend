<?php

namespace App\Logger;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use DateTime;
use DateTimeZone;

class FileLogger implements LoggerInterface
{
    private string $logFile;

    public function __construct(string $filePath)
    {
        $this->logFile = $filePath;
    }
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $validLevels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG
        ];

        if (!in_array($level, $validLevels, true)) {
            throw new InvalidArgumentException("Invalid log level: $level");
        }

        $message = $this->interpolate($message, $context);
        $date = date('Y-m-d H:i:s');
        $date = (new DateTime('now', new DateTimeZone('Asia/Kolkata')))->format('Y-m-d H:i:s');
        $logEntry = "[$date] [$level] $message" . PHP_EOL;

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $value) {
            if (!is_array($value) && (!is_object($value)) || method_exists($value, '__toString')) {
                $replace['{' . $key . '}'] = $value;
            }
        }

        return strtr($message, $replace);
    }
}
