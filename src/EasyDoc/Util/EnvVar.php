<?php

namespace EasyDoc\Util;

class EnvVar
{
    protected $value;

    public function __construct(string $var)
    {
        static $settings = null;

        if ($settings === null) {
            $settings = file_exists('.env') ? parse_ini_file('.env') : [];
            echo (file_exists('.env') ? '.env file loaded: '.var_export(array_keys($settings), true) : 'no .env file.')."\n";
        }

        $this->value = getenv($var);

        if ($this->value === false) {
            $this->value = isset($settings[$var]) ? $settings[$var] : null;
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

    public static function toString($var)
    {
        return (new static($var))->__toString();
    }
}
