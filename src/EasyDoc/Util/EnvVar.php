<?php

namespace EasyDoc\Util;

class EnvVar
{
    /**
     * @var array|null
     */
    protected static $settings = null;

    /**
     * @var string|null
     */
    protected $value;

    public function __construct(string $var)
    {
        if (static::$settings === null) {
            static::$settings = file_exists('.env') ? parse_ini_file('.env') : [];
            echo (file_exists('.env') ? '.env file loaded:'.implode('', array_map(function ($key) {
                return "\n - $key";
            }, array_keys(static::$settings))) : 'no .env file.')."\n";
        }

        $this->value = getenv($var);

        if ($this->value === false) {
            $this->value = isset(static::$settings[$var]) ? static::$settings[$var] : null;
        }
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value ?: '';
    }

    public static function reset(): void
    {
        static::$settings = null;
    }

    public static function toString($var)
    {
        return (new static($var))->__toString();
    }
}
