<?php


namespace ETML;

require_once __DIR__ . '/Email.php';
require_once __DIR__ . '/Template.php';

class ETML
{
	const VERSION	= '0.2';

	/**
	 * getTemplate
	 * 
	 * @static
	 *
	 * @param  string $name 
	 *
	 * @return Template
	 */
	public static function getTemplate(string $name = '')
	{
		if(empty($name))
		{
			return new Template($name);
		}

		return Template::get($name);
	}

	/**
	 * getEmail
	 * 
	 * @static
	 *
	 * @param  string $name
	 *
	 * @return Email
	 */
	public static function getEmail(string $name = '')
	{
		return new Email();
	}
}

/**
 * dj
 * 
 * Debug function
 *
 * @param  mixed $var
 *
 * @return void
 */
function dj($var)
{
	die("<pre>" . print_r($var, true) . "</pre>");
}