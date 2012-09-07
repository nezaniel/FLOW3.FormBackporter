<?php
declare(ENCODING = 'utf-8');
namespace TYPO3\FormBackporter;

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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Extended backporter main class
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Backporter extends \TYPO3\Backporter\Backporter {
	
	/**
	 * Loads all files in $sourcePath, transforms and stores them in $targetPath
	 *
	 * @param string $sourcePath Absolute path of the source file directory
	 * @param string $targetPath Absolute path of the target file directory
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function processFiles($sourcePath, $targetPath) {
		$this->setSourcePath($sourcePath);
		$this->setTargetPath($targetPath);
		if ($this->emptyTargetPath) {
			\TYPO3\FLOW3\Utility\Files::emptyDirectoryRecursively($this->targetPath);
		}
		$this->findSourceFilenames();

		$codeProcessor = $this->objectManager->get($this->codeProcessorClassName);
		$codeProcessor->setExtensionKey($this->extensionKey);

		$unusedReplacePairs = $this->replacePairs;
		foreach($this->sourceFilenames as $sourceFilename) {
			$classCode = \TYPO3\FLOW3\Utility\Files::getFileContents($sourceFilename);
			$relativeFilePath = substr($sourceFilename, strlen($this->sourcePath) + 1);

			if (!$this->shouldFileBeProcessed($relativeFilePath)) {
				continue;
			}
			$targetFilename = \TYPO3\FLOW3\Utility\Files::concatenatePaths(array($this->targetPath, $relativeFilePath));
			$targetFilename = $this->renameTargetFilename($targetFilename);
			\TYPO3\FLOW3\Utility\Files::createDirectoryRecursively(dirname($targetFilename));
		
			$codeProcessor->setClassCode($classCode);

			$fileSpecificReplacePairs = array();
			$unusedFileSpecificReplacePairs = array();
			if (isset($this->fileSpecificReplacePairs[$relativeFilePath]) && is_array($this->fileSpecificReplacePairs[$relativeFilePath])) {
				$fileSpecificReplacePairs = $this->fileSpecificReplacePairs[$relativeFilePath];
				$unusedFileSpecificReplacePairs = $fileSpecificReplacePairs;
			}
			file_put_contents($targetFilename, $codeProcessor->processCode($this->replacePairs, $fileSpecificReplacePairs, $unusedReplacePairs, $unusedFileSpecificReplacePairs));
			if (count($unusedFileSpecificReplacePairs)) {
				/*\TYPO3\FLOW3\var_dump(
					$unusedFileSpecificReplacePairs,
					'Unused file specific replace pairs'
				);*/
				//echo '--- Unused file specific replace pairs: ' . $relativeFilePath . chr(10);
				//var_dump($unusedFileSpecificReplacePairs);
			}
		}
		// Additional classes
		
		if (count($unusedReplacePairs)) {
			/*\TYPO3\FLOW3\var_dump(
				$unusedReplacePairs,
				'Unused replace pairs'
			);*/
			//echo '--- Unused replace pairs: ' . chr(10);
			//var_dump($unusedReplacePairs);
		}
	}
	
	/**
	 * Writes additional files
	 * 
	 * @return void 
	 */
	public function writeAdditionalFiles() {
		$additionalFileData = array(
			'/Classes/Validation/ConjunctionValidator.php' => '<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Bernhard Schmitt <b.schmitt@core4.de>, core4 Kreativagentur
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * The extended conjunction validator
 *
 * @package '.$this->extensionKey.'
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_'.\TYPO3\FormBackporter\Utility\Extension::extKeyToName($this->extensionKey).'_Validation_ConjunctionValidatorAbc extends Tx_Extbase_Validation_Validator_ConjunctionValidator {

	/**
	 * Returns the ConjunctionValidator\'s sub validators
	 * 
	 * @return  Tx_Extbase_Persistence_ObjectStorage<Tx_Extbase_Validation_Validator_ValidatorInterface>
	 */
	public function getValidators() {
		return $this->validators;
	}
	
}

?>',
			'/Classes/ViewHelpers/Form/CheckboxViewHelper.php' => '<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Bernhard Schmitt <b.schmitt@core4.de>, core4 Kreativagentur
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * The extended checkbox view helper
 *
 * @package '.$this->extensionKey.'
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_'.\TYPO3\FormBackporter\Utility\Extension::extKeyToName($this->extensionKey).'_ViewHelpers_Form_CheckboxViewHelper extends Tx_Fluid_ViewHelpers_Form_CheckboxViewHelper {

	/**
	 * Returns the sanitized property value.
	 * 
	 * @return boolean 
	 */
	public function getPropertyValue() {
		$propertyValue = parent::getPropertyValue();
		if ($propertyValue === NULL)
			return FALSE;
		return $propertyValue;
	}

}

?>'
		);
		
		foreach ($additionalFileData as $relativeFilePath => $contents) {
			file_put_contents($this->targetPath.$relativeFilePath, $contents);
			file_put_contents(
				$this->targetPath.$relativeFilePath,
				str_replace('_Original', '', file_get_contents($this->targetPath.$relativeFilePath))
			);
		}
	}
	
	/**
	 * Adds classes from other packages to the backport
	 * 
	 * @return void 
	 */
	public function hijackOtherPackageFiles() {
		$unusedReplacePairs = array();
		$unusedFileSpecificReplacePairs = array();
		$f3SourcePath = substr($this->sourcePath, 0, strpos($this->sourcePath, 'Packages/')+9);
		
		$codeProcessor = $this->objectManager->get($this->codeProcessorClassName);
		$codeProcessor->setExtensionKey($this->extensionKey);
		
		$files = array(
			'ButtonViewHelper'	=> array(
				'source'	=> $f3SourcePath.'Framework/TYPO3.Fluid/Classes/ViewHelpers/Form/ButtonViewHelper.php',
				'target'	=> '/Classes/ViewHelpers/Form/ButtonViewHelper.php',
				'replace'	=> array(
					'\TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper'	=> 'Tx_Fluid_ViewHelpers_Form_AbstractFormFieldViewHelper'
				)
			),
			'ArrayUtility'		=> array(
				'source'	=> $f3SourcePath.'Framework/TYPO3.FLOW3/Classes/Utility/Arrays.php',
				'target'	=> '/Classes/Utility/Arrays.php',
				'replace'	=> array(
				)
			)
		);
		
		foreach ($files as $transformationData) {
			$classCode = \TYPO3\FLOW3\Utility\Files::getFileContents($transformationData['source']);
			$codeProcessor->setClassCode($classCode);
			file_put_contents($this->targetPath.$transformationData['target'], $codeProcessor->processCode($this->replacePairs, $transformationData['replace'], $unusedReplacePairs, $unusedFileSpecificReplacePairs));
		}
	}
	
	/**
	 * Performs special transformations
	 * 
	 * @return void 
	 */
	public function transformSpecial() {
		// FormRuntime
		file_put_contents(
			$this->targetPath.'/Classes/Core/Runtime/FormRuntime.php',
			preg_replace(
				'/protected function getControllerContext\(\) {[^}]+}/',
				'protected function getControllerContext() {
		$uriBuilder = $this->objectManager->get(\'Tx_Extbase_MVC_Web_Routing_UriBuilder\');
		$controllerContext = $this->objectManager->create(\'Tx_Extbase_MVC_Controller_ControllerContext\');
		$controllerContext->setRequest($this->request);
		$controllerContext->setResponse($this->response);
		$controllerContext->setArguments($this->objectManager->create(\'Tx_Extbase_MVC_Controller_Arguments\', array()));
		$controllerContext->setUriBuilder($uriBuilder);
		$controllerContext->setFlashMessageContainer($this->objectManager->get(\'Tx_Extbase_MVC_Controller_FlashMessages\'));
		return $controllerContext;
	}',
				\TYPO3\FLOW3\Utility\Files::getFileContents($this->targetPath.'/Classes/Core/Runtime/FormRuntime.php')
			)
		);
		
		file_put_contents(
			$this->targetPath.'/Classes/Core/Runtime/FormRuntime.php',
			str_replace(
				'public function initializeObject() {
		$this->request = $this->objectManager->create(\'Tx_Extbase_MVC_Request\',$this->request);
		$this->request->setArgumentNamespace($this->formDefinition->getIdentifier());
		if ($this->request->getParentRequest()->hasArgument($this->request->getArgumentNamespace()) === TRUE && is_array($this->request->getParentRequest()->getArgument($this->request->getArgumentNamespace()))) {
			$this->request->setArguments($this->request->getParentRequest()->getArgument($this->request->getArgumentNamespace()));
		}
		
		$this->initializeFormStateFromRequest();
		$this->initializeCurrentPageFromRequest();

		if ($this->formPageHasBeenSubmitted()) {
			$this->processSubmittedFormValues();
		}
	}',
				'public function initializeObject() {
		$this->initializeFormStateFromRequest();
		if ($this->request->hasArgument(\'formIdentifier\') && $this->request->getArgument(\'formIdentifier\') !== $this->formDefinition->getIdentifier()) {
			$this->formState->setLastDisplayedPageIndex(Tx_'.\TYPO3\FormBackporter\Utility\Extension::extKeyToName($this->extensionKey).'_Core_Runtime_FormState::NOPAGE);
		}
		$this->initializeCurrentPageFromRequest();

		if ($this->formPageHasBeenSubmitted()) {
			$this->processSubmittedFormValues();
		}
	}',
				\TYPO3\FLOW3\Utility\Files::getFileContents($this->targetPath.'/Classes/Core/Runtime/FormRuntime.php')
			)
		);
		
		file_put_contents(
			$this->targetPath.'/Classes/Core/Runtime/FormRuntime.php',
			str_replace(
				'protected function processSubmittedFormValues() {
		$result = $this->mapAndValidatePage($this->lastDisplayedPage);
		if ($result->hasErrors()) {
			$this->currentPage = $this->lastDisplayedPage;
			$this->request->setArgument(\'__submittedArguments\', $this->request->getArguments());
			$this->request->setArgument(\'__submittedArgumentValidationResults\', $result);
		}
	}',
				'protected function processSubmittedFormValues() {
		$result = $this->mapAndValidatePage($this->lastDisplayedPage);
		if ($result->hasErrors()) {
			$this->request->setOriginalRequestMappingResults($result);
			$this->currentPage = $this->lastDisplayedPage;
			$this->request->setArgument(\'__submittedArguments\', $this->request->getArguments());
			$this->request->setArgument(\'__submittedArgumentValidationResults\', $result);
		}
	}',
				\TYPO3\FLOW3\Utility\Files::getFileContents($this->targetPath.'/Classes/Core/Runtime/FormRuntime.php')
			)
		);
		
		
		// Array Utility
		file_put_contents(
			$this->targetPath.'/Classes/Utility/Arrays.php',
			str_replace(
				'class Tx_FormBase_Utility_Arrays implements t3lib_Singleton {',
				'class Tx_FormBase_Utility_Arrays implements t3lib_Singleton {

	/**
	 * Validates the given $arrayToTest by checking if an element is not in $allowedArrayKeys.
	 *
	 * @param array $arrayToTest
	 * @param array $allowedArrayKeys
	 * @throws Tx_Flow3FormApi_Exception_TypeDefinitionNotValidException if an element in $arrayToTest is not in $allowedArrayKeys
	 */
	public static function assertAllArrayKeysAreValid(array $arrayToTest, array $allowedArrayKeys) {
		$notAllowedArrayKeys = array_keys(array_diff_key($arrayToTest, array_flip($allowedArrayKeys)));
		if (count($notAllowedArrayKeys) !== 0) {
			throw new Tx_Flow3FormApi_Exception_TypeDefinitionNotValidException(sprintf(\'The options "%s" were not allowed (allowed were: "%s")\', implode(\', \', $notAllowedArrayKeys), implode(\', \', $allowedArrayKeys)), 1325697085);
		}
	}',
				\TYPO3\FLOW3\Utility\Files::getFileContents($this->targetPath.'/Classes/Utility/Arrays.php')
			)
		);
		
		
		// Form definition
		file_put_contents(
			$this->targetPath.'/Classes/Core/Model/FormDefinition.php',
			str_replace(
				array(
					'throw $this->objectManager->create(\'Tx_FormBase_Exception_IdentifierNotValidException\',\'The given identifier was not a string or the string was empty.\', 1325574803);',
					'$page = $this->objectManager->create(\'$implementationClassName\',$identifier, $typeName);'
				),
				array(
					'throw new Tx_FormBase_Exception_IdentifierNotValidException(\'The given identifier was not a string or the string was empty.\', 1325574803);',
					'$page = $this->objectManager->create($implementationClassName, $identifier, $typeName);'
				),
				\TYPO3\FLOW3\Utility\Files::getFileContents($this->targetPath.'/Classes/Core/Model/FormDefinition.php')
			)
		);
		
		// Form runtime
		file_put_contents(
			$this->targetPath.'/Classes/Core/Runtime/FormRuntime.php',
			str_replace(
				array(
					'$renderer = $this->objectManager->create(\'$rendererClassName\');'
				),
				array(
					'$renderer = $this->objectManager->create($rendererClassName);'
				),
				\TYPO3\FLOW3\Utility\Files::getFileContents($this->targetPath.'/Classes/Core/Runtime/FormRuntime.php')
			)
		);
		
		// Abstract section
		file_put_contents(
			$this->targetPath.'/Classes/Core/Model/AbstractSection.php',
			str_replace(
				array(
					'throw $this->objectManager->create(\'Tx_FormBase_Exception_IdentifierNotValidException\',\'The given identifier was not a string or the string was empty.\', 1325574803);',
					'$element = $this->objectManager->create(\'$implementationClassName\',$identifier, $typeName);'
				),
				array(
					'throw new Tx_FormBase_Exception_IdentifierNotValidException(\'The given identifier was not a string or the string was empty.\', 1325574803);',
					'$element = $this->objectManager->create($implementationClassName, $identifier, $typeName);'
				),
				\TYPO3\FLOW3\Utility\Files::getFileContents($this->targetPath.'/Classes/Core/Model/AbstractSection.php')
			)
		);
		
	}
}

?>