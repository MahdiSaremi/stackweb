<?php

namespace StackWeb\Compilers;

use Closure;
use StackWeb\Compilers\Contracts\Token;

class StringReader
{

    public const int DONT_INCLUDE = 2;
    public const int REPLACE_WITH = 4;

    public int          $offset = 0;
    public readonly int $length;

    public readonly array $lineIndexInfo;

    public function __construct(
        public readonly string $content,
        public readonly string $fileName,
        public readonly ?StringReader $parent = null,
        public readonly int $startLine = 1,
        public readonly int $startIndex = 0,
    )
    {
        $this->length = strlen($this->content);

        $lineIndexInfo = [];
        foreach (str_split($this->content) as $i => $char)
        {
            if ($char == "\n") $lineIndexInfo[] = $i;
        }
        $this->lineIndexInfo = $lineIndexInfo;
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

    public function readAll() : ?string
    {
        if ($this->end()) return null;

        $result = substr($this->content, $this->offset);
        $this->offset = $this->length;

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

            $trig = $trigger($read);
            if ($trig === true)
            {
                $result .= $jump < $step && !$isFirst ? @substr($read, $step - $jump) : $read;
                $this->offset += $jump;
            }
            elseif ($trig === static::DONT_INCLUDE)
            {
                $this->offset += $jump;
            }
            elseif (is_array($trig))
            {
                if ($trig[0] === static::REPLACE_WITH)
                {
                    $result .= $jump < $step && !$isFirst ? @substr($trig[1], $step - $jump) : $trig[1];
                    $this->offset += $jump;
                }
                else
                {
                    throw new \InvalidArgumentException("Unknown returned type");
                }
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
            function ($value) use($trigger)
            {
                $result = $trigger($value);
                return is_bool($result) ? !$result : $result;
            },
            $step, $jump,
            $forceLength,
            $skipBreaker, $includeBreaker,
            $breaker, $broken,
        );
    }

    public function readWhiteSpaces()
    {
        return $this->readWhile(ctype_space(...));
    }

    public function readEscape(
        string $char,
        string $escape = '\\',
        bool   $skipBreaker = true,
        bool   $includeBreaker = false,
        bool   $translate = false,
        ?bool  &$found = false
    )
    {
        if ($this->read(silent: true) === $char)
        {
            return $this->read();
        }

        $skipNext = false;
        return $this->readUntil(
            function($value) use (&$skipNext, $char, $escape, $translate)
            {
                if ($value === $escape)
                {
                    $skipNext = true;
                    return $translate ? static::DONT_INCLUDE : false;
                }

                if ($skipNext)
                {
                    $skipNext = false;
                    return $translate ? [static::REPLACE_WITH, $this->getEscapedValue($value) ?? $escape . $value] : false;
                }

                return $value === $char;
            },
            skipBreaker   : $skipBreaker,
            includeBreaker: $includeBreaker,
            broken        : $found,
        );
    }

    protected function getEscapedValue(string $char)
    {
        return match ($char)
        {
            'n' => "\n",
            'r' => "\r",
            'e' => "\e",
            '0' => "\0",
            't' => "\t",
            'f' => "\f",
            'v' => "\v",
            '"', "'", "\\" => $char,
            default => null,
        };
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

    public function readTrig(
        string $char,
        array  $escapes = [],
        array  $ranges = [],
        bool   $skipBreaker = true,
        bool   $includeBreaker = false,
        ?bool &$found = false,
    )
    {
        $escapes = array_map(fn($escape) => is_array($escape) ? $escape : [$escape], $escapes);
        $escapeChars = array_map(fn($escape) => $escape[0], $escapes);
        $rangeChars = array_map(fn($range) => $range[0], $ranges);
        $result = '';

        while (!$this->end())
        {
            $read = $this->read();

            if (in_array($read, $escapeChars))
            {
                $result .= $read;
                $escape = $escapes[array_search($read, $escapeChars)];
                $result .= $this->readEscape(...$escape, includeBreaker: true);
            }
            elseif (in_array($read, $rangeChars))
            {
                $result .= $read;
                $range = $ranges[array_search($read, $rangeChars)];
                $result .= $this->readRange(...$range, includeBreaker: true);
            }
            elseif ($read === $char)
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
            else
            {
                $result .= $read;
            }
        }

        $found = false;
        return $result;
    }


    public function readCWord()
    {
        return $this->readWhile(fn($value) => ctype_alpha($value) || in_array($value, [1,2,3,4,5,6,7,8,9,0,'_']));
    }

    public function readJWord()
    {
        return $this->readWhile(fn($value) => ctype_alpha($value) || in_array($value, [1,2,3,4,5,6,7,8,9,0,'_','$']));
    }

    public function readHWord()
    {
        return $this->readWhile(fn($value) => ctype_alpha($value) || in_array($value, [1,2,3,4,5,6,7,8,9,0,'_',':','.','-']));
    }

    public function readIf(
        string|array $value,
    ) : ?string
    {
        if (is_string($value))
        {
            if ($this->read(strlen($value), silent: true) == $value)
            {
                $this->offset += strlen($value);
                return $value;
            }

            return null;
        }

        foreach ($value as $val)
        {
            if ($this->read(strlen($val), silent: true) == $val)
            {
                $this->offset += strlen($val);
                return $val;
            }
        }

        return null;
    }

    public function silent(Closure $callback)
    {
        $offset = $this->offset;
        $result = $callback();
        $this->offset = $offset;

        return $result;
    }


    public function getLine()
    {
        if (!$this->lineIndexInfo)
        {
            return $this->startLine;
        }

        foreach ($this->lineIndexInfo as $line => $index)
        {
            if ($index >= $this->offset)
            {
                return $line + $this->startLine;
            }
        }

        return count($this->lineIndexInfo) + $this->startLine;
    }

    public function getIndex()
    {
        return $this->offset + $this->startIndex;
    }

    public function getSuperParent()
    {
        $ptr = $this;
        while ($ptr->parent) $ptr = $ptr->parent;

        return $ptr;
    }

    public function syntaxError(string $message, int $offset = 0)
    {
        $this->offset += $offset;
        $line = $this->getLine();
        // $index = $this->getIndex();
        $this->offset -= $offset;

        $super = $this->getSuperParent();

        throw new SyntaxError(
            sprintf("Syntax Error: %s in [%s] on line %s", $message, $super->fileName, $line),
        );
    }

    public function syntaxErrorOn(Token $token, string $message)
    {
        $super = $this->getSuperParent();
        [$line, $index] = $super->silent(function () use ($super, $token)
        {
            $super->offset = $token->getStartOffset();
            return [$super->getLine(), $super->getIndex()];
        });

        throw new SyntaxError(
            sprintf("Syntax Error: %s in [%s] on line %s", $message, $super->fileName, $line),
        );
    }

}