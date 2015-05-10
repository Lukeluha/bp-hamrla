<?php

namespace App\Filter;

use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Utils\Image;

/**
 * Class TemplateFilters
 * @package App\Helper
 */
class TemplateFilters extends Object
{
	/**
	 * @param $imgPath string
	 * @param $width int
	 * @return string Path to new image with given resolution
	 */
	public static function image($imgPath, $width = null)
	{
		if (!file_exists(IMG_DIR . "/" . $imgPath)) {
			throw new InvalidArgumentException("Image doesn't exists");
		}

		if (!$width) {
			return $imgPath;
		}

		$imgName = substr($imgPath, 0, -4);

		$image = Image::fromFile(IMG_DIR . "/" . $imgPath);

		$newPath = $imgName . "-" . $width . ".jpg";

		if (file_exists(IMG_DIR . $newPath)) {
			return $newPath;
		} else {
			$image->resize($width, null);
			$image->save(IMG_DIR . "/" . $newPath, 100, Image::JPEG);
			return $newPath;
		}
	}

	/**
	 * @param $text string
	 * @return string New text with url replaced with a tags
	 * Source: http://blog.mattheworiordan.com/post/13174566389/url-regular-expression-for-links-with-or-without
	 */
	public static function findUrl($text)
	{
		$textWithLinks = preg_replace('/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[\-;:&=\+\$,\w]+@)?[A-Za-z0-9\.\-]+|(?:www\.|[\-;:&=\+\$,\w]+@)[A-Za-z0-9\.\-]+)((?:\/[\+~%\/\.\w\-_]*)?\??(?:[\-\+=&;%@\.\w_]*)#?(?:[\.\!\/\\\w]*))?)/i', '<a href="//$0" target="_blank">$0</a>', $text);
		return $textWithLinks;
	}




}