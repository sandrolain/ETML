<?php

namespace ETML;

class Template
{
	protected $fileExt		= 'et.xml';
	protected $tagPrefix	= 'et';
	protected $fileSource;
	protected $doc;
	protected $styles		= [];
	protected $tags			= [];

	public function __construct(string $filePath = '')
	{
		if(!empty($filePath))
		{
			$this->load($filePath)->parse();
		}
	}

	public function load(string $filePath = 'base')
	{
		// If parameter is not a path
		if(strpos($filePath, '/') === FALSE)
		{
			$fileName = strpos($filePath, ".{$this->fileExt}") ? $filePath : "{$filePath}.{$this->fileExt}";

			$filePath = __DIR__ . "/tpl/{$fileName}";
		}

		if(is_file($filePath))
		{
			if($fileSource = \file_get_contents($filePath))
			{
				$this->fileSource = $fileSource;
			}
			else
			{
				throw new \Exception("Cannot load template file source", 102);
			}
		}
		else
		{
			throw new \Exception("Template file not found", 101);
		}

		return $this;
	}

	public function parse()
	{
		if(empty($this->fileSource))
		{
			throw new \Exception("Empty file source", 103);
		}

		$num = preg_match_all("@<({$this->tagPrefix}\:([^>]+))>(.*)</\\1>@six", $this->fileSource, $matches, PREG_SET_ORDER);
		

		if($num === FALSE)
		{
			throw new \Exception("Parser RegExp error: " . preg_last_error(), 104);
		}

		foreach($matches as $row)
		{
			// 2 : name
			$name = $row[2];

			if($name == 'css')
			{
				continue;
			}
			
			// 3 : code
			$code = $row[3];

			// obtain and remove style tag
			if(preg_match('@<et:css[^>]*>(.*)</et:css>@six', $code, $m))
			{
				$this->styles[$name] = $m[1];

				$code = str_replace($m[0], '', $code);
			}

			$this->tags[$name] = $code;
		}

		//echo '<pre>' . htmlspecialchars(var_export($this->tags, TRUE));
	}

	public function setTagCode(string $name, string $code, string $style = '')
	{
		$this->tags[$name] = $code;

		if(!empty($style))
		{
			$this->setStyle($name, $style);
		}

		return $this;
	}

	public function getTagCode(string $name)
	{
		return isset($this->tags[$name]) ? $this->tags[$name] : '';
	}

	public function setTagStyle(string $name, string $style)
	{
		$this->styles[$name] = $style;

		return $this;
	}

	public function getTagStyle(string $name)
	{
		return isset($this->styles[$name]) ? $this->styles[$name] : '';
	}
	
	public function getTags()
	{
		return array_keys($this->tags);
	}

	protected static $templates = [];

	public static function get(string $template = '')
	{
		if(!isset(self::$templates[$template]))
		{
			self::$templates[$template] = new Template($template);
		}

		return self::$templates[$template];
	}

}