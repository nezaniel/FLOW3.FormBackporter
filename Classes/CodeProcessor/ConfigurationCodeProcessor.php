<?php
declare(ENCODING = 'utf-8');
namespace TYPO3\FormBackporter\CodeProcessor;

/*                                                                        *
 * This script belongs to the FLOW3 package "FormBackporter".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Default Backporter
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ConfigurationCodeProcessor extends \TYPO3\Backporter\CodeProcessor\AbstractCodeProcessor {

	/**
	 * Processes the FLOW3 configuration code by calling the respective helper methods.
	 *
	 * @param array $replacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @param array $fileSpecificReplacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @param array $unusedReplacePairs an array which should be initialized to the same value as $replacePairs. After calling processCode(), it contains only the $replacePairs which were not used during the replacement.
	 * @param array $unusedFileSpecificReplacePairs an array which should be initialized to the same value as $fileSpecificReplacePairs. After calling processCode(), it contains only the $fileSpecificReplacePairs which were not used during the replacement.
	 * @return string the processed code
	 * @author Bernhard Schmitt <b.schmitt@core4.de>
	 */
	function processCode(array $replacePairs, array $fileSpecificReplacePairs, array &$unusedReplacePairs, array&$unusedFileSpecificReplacePairs) {
		$this->replaceStrings($replacePairs, $fileSpecificReplacePairs, $unusedReplacePairs, $unusedFileSpecificReplacePairs);
		$this->transformClassName();
		$this->transformObjectNames();
		$this->processedClassCode = str_replace(
			array(
				'TYPO3.Form:',
				'TYPO3.FLOW3:'
			),
			array(
				'Tx_'.\TYPO3\FormBackporter\Utility\Extension::extKeyToName($this->extensionKey).'_',
				''
			),
			$this->processedClassCode
		);
		$configurationPrefix = 'plugin.tx_'.str_replace('_', '', $this->extensionKey);
		$configurationArray = \Symfony\Component\Yaml\Yaml::parse($this->processedClassCode);
		$configurationArray = array(
			$configurationPrefix => array(
				'settings' => $configurationArray['TYPO3']['Form']
			)
		);
		unset($configurationArray[$configurationPrefix]['settings']['presets']['default']['validatorPresets']['TYPO3.FLOW3:Count']);
		$this->processedClassCode = \TYPO3\FormBackporter\Utility\TypoScript::writeString($configurationArray);
		
		return $this->processedClassCode;
	}

}
?>