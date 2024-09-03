<?php

namespace StackWeb\Compilers;

use Closure;

class StringReader
{

    public int          $offset = 0;
    public readonly int $length;

    public function __construct(
        public readonly string $content,
    )
    {
        $this->length = strlen($this->content);
    }

    public function end() : bool
    {
        return $this->offset >= $this->length;
    }

    public function read(int $length = 1, bool $forceLength = false, bool $silent = false) : ?string
    {
        if ($this->end())
        {
            return null;
        }

        if ($forceLength && $this->offset + $length > $this->length)
        {
            return null;
        }

        $result = substr($this->content, $this->offset, $length);

        if (!$silent)
        {
            $this->offset += $length;
        }

        return $result;
    }

    public function readWhile(
        Closure $trigger,
        int     $step = 1,
        ?int    $jump = null,
        bool    $forceLength = false,
        bool    $skipBreaker = false,
        bool    $includeBreaker = false,
        ?string &$breaker = null,
        ?bool   &$broken = false,
    ) : string
    {
        $jump ??= $step;
        $result = '';
        $breaker = null;
        $broken = false;

        $isFirst = true;
        while (!$this->end())
        {
            $read = $this->read($step, $forceLength, silent: true);

            if ($read === null)
            {
                break;
            }

            if ($trigger($read))
            {
                $result .= $jump < $step && !$isFirst ? @substr($read, $step - $jump) : $read;
                $this->offset += $jump;
            }
            else
            {
                $breaker = $read;
                $broken = true;

                if ($skipBreaker)
                {
                    $this->offset += $jump;
                }

                if ($includeBreaker)
                {
                    $result .= $jump < $step && !$isFirst ? @substr($read, $step - $jump) : $read;
                }

                break;
            }

            $isFirst = false;
        }

        return $result;
    }

    public function readUntil(
        Closure $trigger,
        int     $step = 1,
        ?int    $jump = null,
        bool    $forceLength = false,
        bool    $skipBreaker = false,
        bool    $includeBreaker = false,
        ?string &$breaker = null,
        ?bool   &$broken = false,
    ) : string
    {
        return $this->readWhile(
            fn($value) => !$trigger($value),
            $step, $jump,
            $forceLength,
            $skipBreaker, $includeBreaker,
            $breaker, $broken,
        );
    }

    public function readEscape(
        string $char,
        string $escape = '\\',
        bool   $skipBreaker = true,
        bool   $includeBreaker = false,
        ?bool  &$found = false
    )
    {
        if ($this->read(silent: true) === $char)
        {
            return $this->read();
        }

        $skipNext = false;
        return $this->readUntil(
            function($value) use (&$skipNext, $char, $escape)
            {
                if ($value === $escape)
                {
                    $skipNext = true;
                    return false;
                }

                if ($skipNext)
                {
                    $skipNext = false;
                    return false;
                }

                return $value === $char;
            },
            skipBreaker   : $skipBreaker,
            includeBreaker: $includeBreaker,
            broken        : $found,
        );
    }

    public function readRange(
        string $open,
        string $close,
        array  $escapes = [],
        bool   $skipBreaker = true,
        bool   $includeBreaker = false,
        ?bool &$found = false,
    )
    {
        $escapes = array_map(fn($escape) => is_array($escape) ? $escape : [$escape], $escapes);
        $escapeChars = array_map(fn($escape) => $escape[0], $escapes);
        $result = '';

        $deep = 0;
        while (!$this->end())
        {
            $read = $this->read();

            if (in_array($read, $escapeChars))
            {
                $result .= $read;
                $escape = $escapes[array_search($read, $escapeChars)];
                $result .= $this->readEscape(...$escape, includeBreaker: true);
            }
            elseif ($read === $open)
            {
                $deep++;
                $result .= $read;
            }
            elseif ($read === $close)
            {
                $deep--;
                if ($deep < 0)
                {
                    if ($includeBreaker)
                    {
                        $result .= $read;
                    }

                    if (!$skipBreaker)
                    {
                        $this->offset--;
                    }

                    $found = true;
                    return $result;
                }

                $result .= $read;
            }
            else
            {
                $result .= $read;
            }
        }

        $found = false;
        return $result;
    }

    public function silent(Closure $callback)
    {
        $offset = $this->offset;
        $result = $callback();
        $this->offset = $offset;

        return $result;
    }

}