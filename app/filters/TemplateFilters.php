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
	 * @param $height int
	 * @return string Path to new image with given resolution
	 */
	public static function image($imgPath, $width = null, $height = null)
	{
		if (!file_exists(IMG_DIR . "/" . $imgPath)) {
			throw new InvalidArgumentException("Image doesn't exists");
		}


		if (!$width && !$height) {
			return $imgPath;
		}

		$imgName = substr($imgPath, 0, -4);

		$image = Image::fromFile(IMG_DIR . "/" . $imgPath);
		$newResolution = Image::calculateSize($image->getWidth(), $image->getHeight(), $width, $height, Image::SHRINK_ONLY | Image::FIT);
		$newPath = $imgName . "-w" . $newResolution[0] . "-h" . $newResolution[1] . ".jpg";

		if (file_exists(IMG_DIR . $newPath)) {
			return $newPath;
		} else {
			$image->resize($width, $height, Image::SHRINK_ONLY | Image::FIT);
			$image->save(IMG_DIR . "/" . $newPath);
			return $newPath;
		}

	}

}