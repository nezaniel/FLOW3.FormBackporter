<?php

declare(ENCODING = 'utf-8');
namespace TYPO3\FormBackporter\Utility;

/* *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                    

/**
 * Extension utility library
 *
 * @package FormBackporter
 * @subpackage Utility
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Extension {
	
	/**
	 * Transforms the given extension key to the corresponding extension name
	 * 
	 * @param string $extKey
	 * @return string 
	 */
	public static function extKeyToName($extKey) {
		$extName = '';
		foreach (explode('_', $extKey) as $part) {
			$extName .= ucfirst($part);
		}
		return $extName;
	}
	
}

?>