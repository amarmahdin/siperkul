<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Load KEY=VALUE pairs from a .env file into getenv()/$_ENV/$_SERVER.
 * Does not overwrite variables that are already set in the environment.
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

			$already = getenv($name);
			if ($already !== FALSE && $already !== '') {
				continue;
			}

			putenv($name . '=' . $value);
			$_ENV[$name] = $value;
			$_SERVER[$name] = $value;
		}

		return TRUE;
	}
}

if (!function_exists('env')) {
	function env($key, $default = NULL)
	{
		$value = getenv($key);
		if ($value === FALSE || $value === '') {
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
