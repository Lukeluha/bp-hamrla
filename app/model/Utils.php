<?php

namespace App\Model;


class Utils
{
	public static function getNewClassName($className, $new = true)
	{
		$chars = str_split($className);

		$newName = "";
		foreach ($chars as $char) {
			if (is_numeric($char)) {
				$char = (int) $char;
				if ($new) {
					$newName .= ++$char;
				} else {
					$newName .= --$char;
				}
			} else {
				$newName .= $char;
			}
		}

		return $newName;
	}
}