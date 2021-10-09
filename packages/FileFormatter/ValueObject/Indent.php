<?php

declare (strict_types=1);
namespace Rector\FileFormatter\ValueObject;

use RectorPrefix20211009\Nette\Utils\Strings;
use Rector\FileFormatter\Exception\InvalidIndentSizeException;
use Rector\FileFormatter\Exception\InvalidIndentStringException;
use Rector\FileFormatter\Exception\InvalidIndentStyleException;
use Rector\FileFormatter\Exception\ParseIndentException;
use Stringable;
/**
 * @see \Rector\Tests\FileFormatter\ValueObject\IndentTest
 */
final class Indent
{
    /**
     * @var array<string, string>
     */
    public const CHARACTERS = [self::SPACE => ' ', self::TAB => "\t"];
    /**
     * @var string
     */
    private const SPACE = 'space';
    /**
     * @var string
     */
    private const TAB = 'tab';
    /**
     * @see https://regex101.com/r/A2XiaF/1
     * @var string
     */
    private const VALID_INDENT_REGEX = '#^( *|\\t+)$#';
    /**
     * @var int
     */
    private const MINIMUM_SIZE = 1;
    /**
     * @see https://regex101.com/r/3HFEjX/1
     * @var string
     */
    private const PARSE_INDENT_REGEX = '/^(?P<indent>( +|\\t+)).*/m';
    /**
     * @var string
     */
    private $string;
    private function __construct(string $string)
    {
        $this->string = $string;
    }
    public function __toString() : string
    {
        return $this->string;
    }
    /**
     * @param string $content
     */
    public static function fromString($content) : self
    {
        $match = \RectorPrefix20211009\Nette\Utils\Strings::match($content, self::VALID_INDENT_REGEX);
        if ($match === null) {
            throw \Rector\FileFormatter\Exception\InvalidIndentStringException::fromString($content);
        }
        return new self($content);
    }
    /**
     * @param int $size
     */
    public static function createSpaceWithSize($size) : self
    {
        return self::fromSizeAndStyle($size, self::SPACE);
    }
    public static function createTab() : self
    {
        return self::fromSizeAndStyle(1, self::TAB);
    }
    /**
     * @param int $size
     * @param string $style
     */
    public static function fromSizeAndStyle($size, $style) : self
    {
        if ($size < self::MINIMUM_SIZE) {
            throw \Rector\FileFormatter\Exception\InvalidIndentSizeException::fromSizeAndMinimumSize($size, self::MINIMUM_SIZE);
        }
        if (!\array_key_exists($style, self::CHARACTERS)) {
            throw \Rector\FileFormatter\Exception\InvalidIndentStyleException::fromStyleAndAllowedStyles($style, \array_keys(self::CHARACTERS));
        }
        $value = \str_repeat(self::CHARACTERS[$style], $size);
        return new self($value);
    }
    /**
     * @param string $content
     */
    public static function fromContent($content) : self
    {
        $match = \RectorPrefix20211009\Nette\Utils\Strings::match($content, self::PARSE_INDENT_REGEX);
        if (isset($match['indent'])) {
            return self::fromString($match['indent']);
        }
        throw \Rector\FileFormatter\Exception\ParseIndentException::fromString($content);
    }
    public function getIndentSize() : int
    {
        return \strlen($this->string);
    }
    public function getIndentStyle() : string
    {
        return $this->startsWithSpace() ? self::SPACE : self::TAB;
    }
    public function getIndentStyleCharacter() : string
    {
        return $this->startsWithSpace() ? self::CHARACTERS[self::SPACE] : self::CHARACTERS[self::TAB];
    }
    private function startsWithSpace() : bool
    {
        return \strncmp($this->string, ' ', \strlen(' ')) === 0;
    }
}
