<?php

namespace App\Model;


class Utils
{
	public static function getNewClassName($className)
	{
		$chars = str_split($className);

		$newName = "";
		foreach ($chars as $char) {
			if (is_numeric($char)) {
				$char = (int) $char;
				$newName .= ++$char;
			} else {
				$newName .= $char;
			}
		}

		return $newName;
	}
}