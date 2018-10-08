<?php

namespace ETML;

/**
 * 
 * @namespace ETML
 * @class Template
 * 
 */
class Template
{
	protected $fileExt		= 'et.htm';
	protected $tagPrefix	= 'e-';
	protected $source;
	protected $doc;
	protected $styles		= [];
	protected $tags			= [];

	public function __construct(string $source = '')
	{
		if(!empty($source))
		{
			if(FALSE !== strpos($source, '<'))
			{
				$this->loadFromSource($source);
			}
			else
			{
				$this->loadFromFile($source)->parse();
			}
		}
	}

	public function loadFromSource(string $source)
	{
		if(empty($source))
		{
			throw new \Exception("Template source can not be empty", 105);
		}

		$this->source = $source;
	}

	public function loadFromFile(string $filePath = 'base')
	{
		// If parameter is not a path
		if(strpos($filePath, '/') === FALSE)
		{
			$fileName = strpos($filePath, ".{$this->fileExt}") ? $filePath : "{$filePath}.{$this->fileExt}";

			$filePath = __DIR__ . "/tpl/{$fileName}";
		}

		if(!is_file($filePath))
		{
			throw new \Exception("Template file not found", 101);
		}

		$fileSource = \file_get_contents($filePath);

		if(!$fileSource)
		{
			throw new \Exception("Cannot load template file source", 102);
		}

		$this->loadFromSource($fileSource);

		return $this;
	}

	public function parse()
	{
		if(empty($this->source))
		{
			throw new \Exception("Template source can not be empty", 103);
		}

		$tagPrefix = preg_quote($this->tagPrefix);

		$num = preg_match_all("@<({$tagPrefix}([^>]+))>(.*)</\\1>@six", $this->source, $matches, PREG_SET_ORDER);		

		if($num === FALSE)
		{
			throw new \Exception("Parser RegExp error: " . preg_last_error(), 104);
		}

		foreach($matches as $row)
		{
			// 1 : name
			$name = $row[1];

			if($name == 'css')
			{
				continue;
			}
			
			// 3 : code
			$code	= $row[3];

			$style	= '';
			
			// obtain and remove css style tag
			if(preg_match("@<(style)(\s+[^>]*)?>(.*)</\\1>@six", $code, $m))
			{
				$style	= $m[3];

				$code	= str_replace($m[3], '', $code);
			}

			$this->setTagCode($name, $code, $style);
		}

		return $this;
	}

	public function setTagCode(string $name, string $code, string $style = '')
	{
		$this->tags[$name] = $code;

		if(!empty($style))
		{
			$this->setTagStyle($name, $style);
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
		$tags = array_keys($this->tags);

		sort($tags);

		return $tags;
	}

	////////////////////////////////////////////////////////////////
	// Static Properties and Methods

	protected static $templates = [];

	/**
	 * get
	 *
	 * @static
	 * 
	 * @param string $source
	 *
	 * @return Template instance
	 */
	public static function get(string $source = '')
	{
		$sourceMd5 = md5($source);

		if(!isset(self::$templates[$sourceMd5]))
		{
			self::$templates[$sourceMd5] = new Template($source);
		}

		return self::$templates[$sourceMd5];
	}

}