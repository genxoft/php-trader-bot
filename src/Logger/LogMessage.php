<?php

declare(strict_types=1);

namespace App\Logger;

use DateTime;
use Stringable;

class LogMessage
{
    public readonly ?DateTime $time;

    public function __construct(
        public readonly mixed $level,
        public readonly string|Stringable $message,
        public readonly array $context,
        ?DateTime $time = null,
        public readonly ?array $backtrace = null,
    ) {
        if ($time !== null) {
            $this->time = $time;
        } else {
            $this->time = new DateTime('now');
        }
    }

    public function parseMessage(): string
    {
        $replace = array();
        foreach ($this->context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($this->message, $replace);
    }

    public function parseTrace(): string
    {
        if ($this->backtrace === null) {
            return '';
        }
        $traceStr = '';
        $i = 0;
        foreach ($this->backtrace as $trace) {
            if (isset($trace['class'])) {
                $func = $trace['class'] . $trace['type'] . $trace['function'];
            } else {
                $func = $trace['function'];
            }
            $traceStr .= sprintf("#%d %s (%d): %s", $i, $trace['file'], $trace['line'], $func) . PHP_EOL;
            $i++;
        }
        return $traceStr;
    }
}
