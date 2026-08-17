<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Load KEY=VALUE pairs from a .env file into $_ENV/$_SERVER/(putenv if allowed).
 * Does not overwrite variables that are already set.
 *
 * @param string $path Absolute path to .env file
 * @return bool
 */
if (!function_exists('load_env_file')) {
	function load_env_file($path)
	{
		if (!is_file($path) || !is_readable($path)) {
			return FALSE;
		}

		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if ($lines === FALSE) {
			return FALSE;
		}

		foreach ($lines as $line) {
			$line = trim($line);
			if ($line === '' || strpos($line, '#') === 0) {
				continue;
			}

			if (strpos($line, '=') === FALSE) {
				continue;
			}

			list($name, $value) = explode('=', $line, 2);
			$name = trim($name);
			$value = trim($value);

			if ($name === '') {
				continue;
			}

			// Strip optional surrounding quotes
			if (
				(strlen($value) >= 2) &&
				(($value[0] === '"' && substr($value, -1) === '"') ||
				 ($value[0] === "'" && substr($value, -1) === "'"))
			) {
				$value = substr($value, 1, -1);
			}

			if (siperkul_env_raw($name) !== NULL) {
				continue;
			}

			// putenv() often disabled on shared hosting (InfinityFree, etc.)
			if (function_exists('putenv')) {
				@putenv($name . '=' . $value);
			}
			$_ENV[$name] = $value;
			$_SERVER[$name] = $value;
		}

		return TRUE;
	}
}

/**
 * Read raw env value without type casting. Checks $_ENV, $_SERVER, then getenv.
 *
 * @param string $key
 * @return string|null
 */
if (!function_exists('siperkul_env_raw')) {
	function siperkul_env_raw($key)
	{
		if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
			return (string) $_ENV[$key];
		}
		if (array_key_exists($key, $_SERVER) && $_SERVER[$key] !== '') {
			return (string) $_SERVER[$key];
		}
		$value = getenv($key);
		if ($value !== FALSE && $value !== '') {
			return (string) $value;
		}
		return NULL;
	}
}

if (!function_exists('env')) {
	function env($key, $default = NULL)
	{
		$value = siperkul_env_raw($key);
		if ($value === NULL) {
			return $default;
		}

		switch (strtolower($value)) {
			case 'true':
			case '(true)':
				return TRUE;
			case 'false':
			case '(false)':
				return FALSE;
			case 'null':
			case '(null)':
				return NULL;
			default:
				return $value;
		}
	}
}
