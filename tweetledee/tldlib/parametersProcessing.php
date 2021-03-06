<?php

/*******************************************************************
 *   Parameters
 *    - can pass via URL to web server
 *    - or as a short or long switch at the command line
 ********************************************************************/

/**
 * Possible parameters are defined here
 */
const NAME = "name";

const SHORT = "short";

const TYPE = "type";

const VALIDATION = "validation";

const MIN = "min";

const MAX = "max";

const LONG = "long";

const INT = "int";

const BOOL = "bool";
const DEFAULT_VALUE = "default";

const PARAMETERS = [
    "c" => [
        NAME => "count",
        SHORT => "c",
        TYPE => INT,
        VALIDATION => [
            MIN => 0,
            MAX => 200
        ],
        DEFAULT_VALUE => 25
    ],
    "cache_interval" => [
        NAME => "cache_interval",
        LONG => "cache_interval",
        TYPE => INT,
        VALIDATION => [
            MIN => 0,
            MAX => PHP_INT_MAX
        ],
        DEFAULT_VALUE => 90
    ],
    "exclude_replies" => [
        NAME => "exclude_replies",
        LONG => "xrp",
        TYPE => BOOL,
        DEFAULT_VALUE => false
    ],
    "exclude_retweets" => [
        NAME => "exclude_retweets",
        LONG => "xrt",
        TYPE => BOOL,
        DEFAULT_VALUE => false
    ],
    "list" => [
        NAME => "list",
        LONG => "list"
    ],
    "query" => [
        NAME => "query",
        SHORT => "q",
        LONG => "query"
    ],
    "recursion_limit" => [
        NAME => "recursion_limit",
        SHORT => "rl",
        LONG => "recursion_limit",
        TYPE => INT,
        VALIDATION => [
            MIN => 0,
            MAX => 100
        ],
        DEFAULT_VALUE => 0
    ],
    "result_type" => [
        NAME => "result_type",
        LONG => "rt",
        VALIDATION => ['popular', 'recent'],
        DEFAULT_VALUE => "mixed"
    ],
    "user" => [
        NAME => "screen_name",
        LONG => "user"
    ]
];

function parameters_from($parameter_names)
{
    $parameters_objects = [];
    foreach ($parameter_names as $name) {
        if (array_key_exists($name, PARAMETERS)) {
            array_push($parameters_objects, PARAMETERS[$name]);
        }
    }
    return $parameters_objects;
}

function extract_value($type, $definition, $params)
{
    $extracted = $params[$definition[$type]];
    if (isset($definition[TYPE]) && $definition[TYPE] == INT) {
        $value = intval($extracted);
        if (array_key_exists(VALIDATION, $definition)) {
            if ($value >= $definition[VALIDATION][MIN] && $value <= $definition[VALIDATION][MAX]) {
                return $value;
            } else {
                return null;
            }
        }
    } elseif (isset($definition[TYPE]) && $definition[TYPE] == BOOL) {
        $value = filter_var($extracted, FILTER_VALIDATE_BOOLEAN);
        return $value;
    } else {
        if (array_key_exists(VALIDATION, $definition)) {
            if (in_array($value, $definition[VALIDATION])) {
                return $value;
            } else {
                return null;
            }
        } else {
            return $extracted;
        }
    }
}

function load_parameters_from_command_line($parameters_definitions)
{
    $returned = [];
    if (isset($argv)) {
        $shortopts = "";
        $longopts = [];
        foreach ($parameters_definitions as $definition) {
            if (array_key_exists(SHORT, $definition)) {
                $shortopts = $shortopts . $definition[SHORT];
            }
            if (array_key_exists(LONG, $definition)) {
                array_push($longopts, $definition[LONG]);
            }
        }
        $params = getopt($shortopts, $longopts);
        foreach ($parameters_definitions as $definition) {
            if (array_key_exists(SHORT, $definition)) {
                if (array_key_exists($definition[SHORT], $params)) {
                    $returned[$definition[NAME]] = extract_value(SHORT, $definition, $params);
                }
            }
            if (array_key_exists(LONG, $definition)) {
                if (array_key_exists($definition[LONG], $params)) {
                    $returned[$definition[NAME]] = extract_value(LONG, $definition, $params);
                }
            }
        }
    }
    return $returned;
}

function load_parameters_from_http_request($parameters_definitions)
{
    $returned = [];
    foreach ($parameters_definitions as $definition) {
        if (array_key_exists(SHORT, $definition)) {
            if (array_key_exists($definition[SHORT], $_GET)) {
                $returned[$definition[NAME]] = extract_value(SHORT, $definition, $_GET);
            }
        }
        if (array_key_exists(LONG, $definition)) {
            if (array_key_exists($definition[LONG], $_GET)) {
                $returned[$definition[NAME]] = extract_value(LONG, $definition, $_GET);
            }
        }
        // No value was found ?
        if (!array_key_exists($definition[NAME], $returned)) {
            // Then it's time for default !
            if (array_key_exists(DEFAULT_VALUE, $definition)) {
                $returned[$definition[NAME]] = $definition[DEFAULT_VALUE];
            }
        }
    }
    return $returned;
}

/**
 * Load parameter from request or command line
 *
 * @param array $parameter_names
 *            values given here must be keys in PARAMETERS constant
 * @return array an array containg the parameters and their values as a dict
 */
function load_parameters($parameter_names)
{
    $parameters_definitions = parameters_from($parameter_names);
    // Command line parameter definitions //
    if (defined('STDIN')) {
        return load_parameters_from_command_line($parameters_definitions);
    } // end if
      // Web server URL parameter definitions //
    else {
        return load_parameters_from_http_request($parameters_definitions);
    } // end else
}
