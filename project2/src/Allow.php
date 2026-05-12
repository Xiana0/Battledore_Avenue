<?php

namespace PHPMaker2026\Project1;

// Allow
enum Allow: int
{
    case ADD = 1;
    case DELETE = 2;
    case EDIT = 4;
    case LIST = 8;
    case ACCESS = 16;
    case VIEW = 32;
    case SEARCH = 64;
    case IMPORT = 128;
    case LOOKUP = 256;
    case PUSH = 512;
    case EXPORT = 1024;
    case GRANT = 2048;
    case ADMIN = 16777215;

    public static function privileges(): array
    {
        $arr = array_change_key_case(array_column(self::cases(), "value", "name"));
        return $arr;
    }
}
