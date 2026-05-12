<?php

namespace PHPMaker2026\Project1;

// Action type
enum ActionType: string
{
    case POSTBACK = "P";
    case REDIRECT = "R";
    case AJAX = "A";
    case MULTIPLE = "M";
    case SINGLE = "S";
    case CUSTOM = "C";
}
