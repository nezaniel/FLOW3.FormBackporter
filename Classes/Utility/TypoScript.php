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
 * TypoScript utility library
 *
 * @package FormBackporter
 * @subpackage Utility
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TypoScript {
	
	/**
	 * Transforms YAML code to TypoScript code
	 * 
	 * @param string $yaml
	 * @param integer $ind The indentation offset
	 * @return string 
	 */
	public static function yaml2Ts ($yaml, $ind = 0) {
		return self::writeString(\Symfony\Component\Yaml\Yaml::parse($yaml), $ind);
	}
	
	/**
     * Writes a given data array to a TypoScript string
     *
     * @param array $dataArray The data array
     * @return string The TypoScript string
     */
    public static function writeString($dataArray, $ind = 0) {
        $TSString = '';
        foreach($dataArray as $k => $v) {
            $TSString .= str_repeat("\t", $ind);
            if(is_array($v)) {
                $TSString .= $k.' {'."\r\n".self::writeString($v,$ind+1).str_repeat("\t", $ind)."}\r\n";
            } else {
				$TSString .= $k.' = '.$v."\r\n";
			}
        }
        return $TSString;
    }
	
}

?>