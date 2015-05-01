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

}