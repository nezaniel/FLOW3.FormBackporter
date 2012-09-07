<?php
declare(ENCODING = 'utf-8');
namespace TYPO3\FormBackporter\CodeProcessor;

/*                                                                        *
 * This script belongs to the FLOW3 package "Backporter".                 *
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

/**
 * Default Backporter
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class FormClassCodeProcessor extends \TYPO3\Backporter\CodeProcessor\AbstractCodeProcessor {
	
	

	/**
	 * Processes the FLOW3 code by calling the respective helper methods.
	 *
	 * @param array $replacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @param array $fileSpecificReplacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @param array $unusedReplacePairs an array which should be initialized to the same value as $replacePairs. After calling processCode(), it contains only the $replacePairs which were not used during the replacement.
	 * @param array $unusedFileSpecificReplacePairs an array which should be initialized to the same value as $fileSpecificReplacePairs. After calling processCode(), it contains only the $fileSpecificReplacePairs which were not used during the replacement.
	 * @return string the processed code
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	function processCode(array $replacePairs, array $fileSpecificReplacePairs, array &$unusedReplacePairs, array&$unusedFileSpecificReplacePairs) {
		$this->replaceStrings($replacePairs, $unusedReplacePairs);
		$this->replaceStrings($fileSpecificReplacePairs, $unusedFileSpecificReplacePairs);
		$this->removeEncodingDeclaration();
		$this->removeNamespaceDeclarations();
		$this->removeGlobalNamespaceSeparators();
		$this->removeUseStatements();
		$this->addPackageAndSubpackageAnnotations();
		$this->transformClassName();
		$this->processScopeAnnotation();
		$this->transformObjectNames();
		$this->transformToObjectManagerUse();
		return $this->processedClassCode;
	}
	
	/**
	 * Transforms "new" calls to ObjectManager calls
	 * 
	 * @return void
	 */
	protected function transformToObjectManagerUse() {
		$matches = array();
		preg_match_all('/new [a-zA-z$_0-9]+\([^\)]*\);/', $this->processedClassCode, $matches);
		
		if (sizeof($matches[0]) > 0) {
			foreach ($matches[0] as $match) {
				$modification = str_replace(
					array('(',	'new ',	',)'),
					array('\',', '$this->objectManager->create(\'',	')'),
					$match
				);
				$this->processedClassCode = str_replace($match, $modification, $this->processedClassCode);
			}
			$classMatches = array();
				preg_match_all('/class Tx_[a-zA-z_0-9, ]+\{/', $this->processedClassCode, $classMatches);
				if (sizeof($classMatches[0]) > 0) {
					$classMatch = $classMatches[0][0];
					$classModification = $classMatch.'
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;';
					$this->processedClassCode = str_replace($classMatch, $classModification, $this->processedClassCode);
				}
		}
	}

}
?>