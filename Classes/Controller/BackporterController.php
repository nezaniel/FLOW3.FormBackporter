<?php
declare(ENCODING = 'utf-8');
namespace TYPO3\FormBackporter\Controller;

/*                                                                        *
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
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Packporter Default Controller
 *
 * @package FluidBackporter
 * @subpackage Command
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class BackporterController extends \TYPO3\FLOW3\Mvc\Controller\ActionController {

	/**
	 * @var \TYPO3\FLOW3\Package\PackageManagerInterface
	 * @FLOW3\Inject
	 */
	protected $packageManager;

	/**
	 * @var \TYPO3\FormBackporter\Backporter
	 * @FLOW3\Inject
	 */
	protected $backporter;

	/**
	 * The settings
	 * 
	 * @var array
	 */
	protected $settings;
	
	/**
	 * The extension key
	 * 
	 * @var string 
	 */
	protected $extensionKey = 'form_base';
	
	/**
	 * The class prefix
	 * 
	 * @var string
	 */
	protected $classPrefix = '';
	
	/**
	 * Returns the class prefix
	 * 
	 * @return string
	 */
	protected function getClassPrefix() {
		if ($this->classPrefix === '') {
			$this->classPrefix = 'Tx_';
			foreach (explode('_', $this->extensionKey) as $prefixPart) {
				$this->classPrefix .= ucfirst($prefixPart);
			}
			$this->classPrefix .= '_';
		}
		return $this->classPrefix;
	}

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * The backport action
	 * 
	 * @return string 
	 */
	public function backportAction() {
		$this->backporter->emptyTargetPath($this->settings['emptyTargetDirectory']);
		$this->backporter->setExtensionKey($this->extensionKey);
		
		$this->processClassFiles();
		$this->processConfigurationFiles();
		$this->processTemplateFiles();
		$this->hijackOtherPackageFiles();
		$this->processAdditionalFiles();
		$this->transformSpecial();
		
		return 'Files backported and stored in "' . $this->settings['targetPath'] . '"!';
	}

	/**
	 * @return void
	 */
	protected function processClassFiles() {
		$this->backporter->setCodeProcessorClassName('TYPO3\FormBackporter\CodeProcessor\FormClassCodeProcessor');
		$replacePairs = array_merge($this->createClassNameReplacePairs(array(
				'AbstractFormFactory'						=> $this->getClassPrefix().'Factory_AbstractFormFactory',
				'AbstractRenderable'						=> $this->getClassPrefix().'Core_Model_Renderable_AbstractRenderable',
				'AbstractSection'							=> $this->getClassPrefix().'Core_Model_AbstractSection',
				'ActionRequest'								=> 'Tx_Extbase_MVC_Request',
				'CompositeRenderableInterface'				=> $this->getClassPrefix().'Core_Model_Renderable_CompositeRenderableInterface',
				'FinisherContext'							=> $this->getClassPrefix().'Core_Model_FinisherContext',
				'FinisherInterface'							=> $this->getClassPrefix().'Core_Model_FinisherInterface',
				'FormDefinition'							=> $this->getClassPrefix().'Core_Model_FormDefinition',
				'FormElementInterface'						=> $this->getClassPrefix().'Core_Model_FormElementInterface',
				'FormFactoryInterface'						=> $this->getClassPrefix().'Factory_FormFactoryInterface',	
				'FormPersistenceManagerInterface'			=> $this->getClassPrefix().'Persistence_FormPersistenceManagerInterface',
				'FormRuntime'								=> $this->getClassPrefix().'Core_Runtime_FormRuntime',	
				'FormState'									=> $this->getClassPrefix().'Core_Runtime_FormState',
				'GenericFormElement'						=> $this->getClassPrefix().'FormElements_GenericFormElement',
				'Page'										=> $this->getClassPrefix().'Core_Model_Page',
				'ProcessingRule'							=> $this->getClassPrefix().'Core_Model_ProcessingRule',
				'Renderable\AbstractCompositeRenderable'	=> $this->getClassPrefix().'Core_Model_Renderable_AbstractCompositeRenderable',
				'Renderable\AbstractRenderable'				=> $this->getClassPrefix().'Core_Model_Renderable_AbstractRenderable',
				'Renderable\RenderableInterface'			=> $this->getClassPrefix().'Core_Model_Renderable_RenderableInterface',
				'Renderable\CompositeRenderableInterface'	=> $this->getClassPrefix().'Core_Model_Renderable_CompositeRenderableInterface',
				'RenderableInterface'						=> $this->getClassPrefix().'Core_Model_Renderable_RenderableInterface',
				'RendererInterface'							=> $this->getClassPrefix().'Core_Renderer_RendererInterface',	
				'RootRenderableInterface'					=> $this->getClassPrefix().'Core_Model_Renderable_RootRenderableInterface',
				'SupertypeResolver'							=> $this->getClassPrefix().'Utility_SupertypeResolver'
			)),
			array(
				'\TYPO3\FLOW3\Cache\Frontend\PhpFrontend'						=> 't3lib_cache_frontend_PhpFrontend',
				'\TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper'				=> 'Tx_Fluid_Core_ViewHelper_AbstractViewHelper',
				'TYPO3\Fluid\Core\Parser\Interceptor\Escape'					=> 'Tx_Fluid_Core_Parser_Interceptor_Escape',
				'\TYPO3\FLOW3\Configuration\ConfigurationManager'				=> 'Tx_Extbase_Configuration_ConfigurationManager',
				'\TYPO3\FLOW3\Exception'										=> 'Tx_Extbase_Exception',
				'\TYPO3\FLOW3\Http\Response'									=> 'Tx_Extbase_MVC_Response',
				'\TYPO3\FLOW3\Mvc\ActionRequest'								=> 'Tx_Extbase_MVC_Request',
				'TYPO3\FLOW3\Mvc\Controller\ControllerContext'					=> 'Tx_Extbase_MVC_Controller_ControllerContext',
				'TYPO3\FLOW3\Mvc\View\ViewInterface'							=> 'Tx_Extbase_MVC_View_ViewInterface',
				'TYPO3\FLOW3\Mvc\Web\Request'									=> 'Tx_Extbase_MVC_Web_Request',
				'TYPO3\FLOW3\Mvc\Web\Routing\UriBuilder'						=> 'Tx_Extbase_MVC_Web_Routing_UriBuilder',
				'TYPO3\FLOW3\Object\ObjectManagerInterface'						=> 'Tx_Extbase_Object_ObjectManagerInterface',
				'TYPO3\FLOW3\Persistence\PersistenceManagerInterface'			=> 'Tx_Extbase_Persistence_ManagerInterface', // FIXME
				'TYPO3\FLOW3\Reflection\ClassReflection'						=> 'Tx_Extbase_Reflection_ClassReflection',
				'TYPO3\FLOW3\Reflection\ObjectAccess'							=> 'Tx_Extbase_Reflection_ObjectAccess',
				'TYPO3\FLOW3\Reflection\ReflectionService'						=> 'Tx_Extbase_Reflection_Service', // FIXME
				'TYPO3\FLOW3\Tests\UnitTestCase'								=> 'Tx_Extbase_Tests_Unit_BaseTestCase',
				'\TYPO3\FLOW3\Utility\Arrays'									=> 'Tx_Extbase_Utility_Arrays',
				'\TYPO3\FLOW3\var_dump'											=> 'Tx_Extbase_Utility_Debugger::var_dump',
				'\TYPO3\FLOW3\Validation\Validator\AbstractValidator'			=> 'Tx_Extbase_Validation_Validator_AbstractValidator',
				'\TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper'		=> 'Tx_Fluid_ViewHelpers_Form_AbstractFormFieldViewHelper',
				'\TYPO3\Fluid\ViewHelpers\FormViewHelper'						=> 'Tx_Fluid_ViewHelpers_FormViewHelper',
				'\TYPO3\SwiftMailer\Message'									=> 't3lib_mail_Message',
				'\TYPO3\Fluid\View\TemplateView'								=> 'Tx_Fluid_View_TemplateView',
				'\TYPO3\FLOW3\Mvc\Controller\Arguments'							=> 'Tx_Extbase_MVC_Controller_Arguments',
				'\TYPO3\FLOW3\Mvc\Routing\UriBuilder'							=> 'Tx_Extbase_MVC_Web_Routing_UriBuilder',
				'\TYPO3\Fluid\Core\Parser\Configuration'						=> 'Tx_Fluid_Core_Parser_Configuration',
				'\TYPO3\Fluid\Core\Parser\Interceptor\Escape'					=> 'Tx_Fluid_Core_Parser_Interceptor_Escape',
				'\TYPO3\FLOW3\Security\Cryptography\HashService'				=> 'Tx_Extbase_Security_Cryptography_HashService',
				'\TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer'		=> 'Tx_Fluid_Core_ViewHelper_TemplateVariableContainer',
				'\TYPO3\FLOW3\Mvc\FlashMessageContainer'						=> 'Tx_Extbase_MVC_Controller_FlashMessages',
				'\TYPO3\FLOW3\Property\PropertyMappingConfiguration'			=> 'Tx_Extbase_Property_PropertyMappingConfiguration',
				'\TYPO3\FLOW3\Validation\Validator\ConjunctionValidator'		=> 'Tx_Extbase_Validation_Validator_ConjunctionValidator',
				'\TYPO3\FLOW3\Property\PropertyMapper'							=> 'Tx_Extbase_Property_PropertyMapper',
				'\TYPO3\FLOW3\Validation\Validator\ValidatorInterface'			=> 'Tx_Extbase_Validation_Validator_ValidatorInterface',
				'\TYPO3\Fluid\View\Exception\InvalidTemplateResourceException'	=> 'Tx_Fluid_View_Exception_InvalidTemplateResourceException',
				'\TYPO3\Fluid\Core\ViewHelper\TagBuilder'						=> 'Tx_Fluid_Core_ViewHelper_TagBuilder',
				'\TYPO3\FLOW3\Validation\Validator\ValidatorInterface'			=> 'Tx_Extbase_Validation_Validator_ValidatorInterface',
				'\TYPO3\FLOW3\Error\Result'										=> 'Tx_Extbase_Error_Result',
				'\TYPO3\FLOW3\Validation\Validator\NotEmptyValidator'			=> 'Tx_Extbase_Validation_Validator_NotEmptyValidator',
				
				'UUID'															=> 'UID',
				'$this->persistenceManager->getIdentifierByObject'				=> '$this->persistenceManager->getBackend()->getIdentifierByObject', // FIXME
				'$this->persistenceManager->isNewObject('						=> '$this->persistenceManager->getBackend()->isNewObject(', // FIXME
				'new \SplObjectStorage()'										=> 't3lib_div::makeInstance(\'Tx_Extbase_Persistence_ObjectStorage\')',
				'\SplObjectStorage'												=> 'Tx_Extbase_Persistence_ObjectStorage',
				'* @FLOW3\Internal'												=> '* @internal',
				'* This script belongs to the FLOW3 package "TYPO3.Form".'		=> '* This script is backported from the FLOW3 package "TYPO3.Form".',
				'* @FLOW3\Inject'												=> '* @inject',
				'TYPO3.Form:'													=> $this->getClassPrefix()
			)
		);
		$this->backporter->setReplacePairs($replacePairs);
		$this->backporter->setIncludeFilePatterns(array(
			'#^Classes/.*$#',
			'#^Resources/.*$#',
			'#^Tests/.*$#',
		));
		$this->backporter->setExcludeFilePatterns(array(
			'#^Classes/Package.php$#',
		));
		$this->backporter->setFileSpecificReplacePairs(array(
			// RenderViewHelper
			'Classes/ViewHelpers/RenderViewHelper.php' => array(
				'\TYPO3\Form\Persistence\FormPersistenceManagerInterface' => $this->getClassPrefix().'Persistence_YamlPersistenceManager',
				'// TOKEN-1'	=> '/**
	 * @inject
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;'
			),
			// FormViewHelper
			'Classes/ViewHelpers/FormViewHelper.php' => array(
				'return \'\'' => 'return $this->renderingContext->getTemplateVariableContainer()->get(\'form\')->getIdentifier();',
			),
			// AbstractFormFactory
			'Classes/Factory/AbstractFormFactory.php' => array(
				'$this->formSettings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, \'TYPO3.Form\');' =>
				'$this->formSettings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, \''.\TYPO3\FormBackporter\Utility\Extension::extKeyToName($this->extensionKey).'\', \'Pi1\');'
			),
			// FormDefinition
			'Classes/Core/Model/FormDefinition.php' => array(
				'* @param Page $page' => '* @param '.$this->getClassPrefix().'Core_Model_Page $page',
				'public function addPage(Page $page) {' => 'public function addPage('.$this->getClassPrefix().'Core_Model_Page $page) {',
				'return new \TYPO3\Form\Core\Runtime\FormRuntime($this, $request, $response);' => '$runtime = $this->objectManager->create(\''.$this->getClassPrefix().'Core_Runtime_FormRuntime\', $this, $request, $response);
		$runtime->initializeObject();
		return $runtime;'
			),
			// FluidFormRenderer
			'Classes/Core/Renderer/FluidFormRenderer.php' => array(
				'list($packageKey, $shortRenderableType) = explode(\':\', $renderableType);

		return strtr($renderingOptions[\'templatePathPattern\'], array(
			\'{@package}\' => $packageKey,
			\'{@type}\' => $shortRenderableType
		));' => '$shortRenderableType = str_replace(\''.$this->getClassPrefix().'\', \'\', $renderableType);

		return strtr($renderingOptions[\'templatePathPattern\'], array(
			\'EXT:\' => \'typo3conf/ext/\',
			\'{@type}\' => $shortRenderableType
		));',
				'// TOKEN-1' => 'public function __construct() {
		parent::__construct();
		$this->injectTemplateCompiler($this->objectManager->get(\'Tx_Fluid_Core_Compiler_TemplateCompiler\'));
	}'
			),
			// FormState
			'Classes/Core/Runtime/Formstate.php' => array(
				'return \TYPO3\FLOW3\Utility\Arrays::getValueByPath($this->formValues, $propertyPath);' => 'return Tx_Extbase_Utility_Arrays::getValueByPath($this->formValues, array($propertyPath));'
			),
			// FormRuntime
			'Classes/Core/Runtime/FormRuntime' => array(
				'protected function getControllerContext() {
		$uriBuilder = new \TYPO3\FLOW3\Mvc\Routing\UriBuilder();
		$uriBuilder->setRequest($this->request);

		return new \TYPO3\FLOW3\Mvc\Controller\ControllerContext(
			$this->request,
			$this->response,
			new \TYPO3\FLOW3\Mvc\Controller\Arguments(array()),
			$uriBuilder,
			$this->flashMessageContainer
		);
	}' => 'protected function getControllerContext() {
		$uriBuilder = t3lib_div::makeInstance(\'Tx_Extbase_MVC_Web_Routing_UriBuilder\');
		$uriBuilder->setRequest($this->request);
		$uriBuilder->injectExtensionService(t3lib_div::makeInstance(\'Tx_Extbase_Service_ExtensionService\'));
		$uriBuilder->injectConfigurationManager(t3lib_div::makeInstance(\'Tx_Extbase_Configuration_ConfigurationManager\'));
		$uriBuilder->initializeObject();

		$controllerContext = new Tx_Extbase_MVC_Controller_ControllerContext();
		$controllerContext->setRequest($this->request);
		$controllerContext->setResponse($this->response);
		$controllerContext->setArguments(new Tx_Extbase_MVC_Controller_Arguments(array()));
		$controllerContext->setUriBuilder($uriBuilder);
		$controllerContext->setFlashMessageContainer(t3lib_div::makeInstance(\'Tx_Extbase_MVC_Controller_FlashMessages\'));
		return $controllerContext;
	}',
				'$value = \TYPO3\FLOW3\Utility\Arrays::getValueByPath($requestArguments, $element->getIdentifier());' =>
				'$value = Tx_Extbase_Utility_Arrays::getValueByPath($requestArguments, array($element->getIdentifier()));',
			)
		));
		$this->backporter->processFiles($this->packageManager->getPackage('TYPO3.Form')->getPackagePath(), $this->settings['targetPath']);
		
	}
	
	/**
	 * @return void 
	 */
	protected function hijackOtherPackageFiles() {
		$this->backporter->setCodeProcessorClassName('TYPO3\FormBackporter\CodeProcessor\FormClassCodeProcessor');
		$this->backporter->hijackOtherPackageFiles();
	}
	
	/**
	 * @return void 
	 */
	protected function processAdditionalFiles() {
		$this->backporter->setCodeProcessorClassName('TYPO3\FormBackporter\CodeProcessor\FormClassCodeProcessor');
		$this->backporter->writeAdditionalFiles();
	}
	
	/**
	 * @return void 
	 */
	protected function transformSpecial() {
		$this->backporter->setCodeProcessorClassName('TYPO3\FormBackporter\CodeProcessor\FormClassCodeProcessor');
		$this->backporter->transformSpecial();
	}

	/**
	 * @return void
	 */
	protected function processTestFiles() {
		$this->backporter->emptyTargetPath(FALSE);
		$replacePairs = array(
			'TYPO3\FLOW3\Cache\Frontend\VariableFrontend' => 't3lib_cache_frontend_VariableFrontend',
			'TYPO3\FLOW3\Error\Error' => 'Tx_Extbase_Error_Error',
			'\TYPO3\FLOW3\Http\Response' => 'Tx_Extbase_MVC_Response',
			'TYPO3\FLOW3\Object\ObjectManagerInterface' => 'Tx_Extbase_Object_ObjectManagerInterface',
			'\TYPO3\FLOW3\Mvc\ActionRequest' => 'Tx_Extbase_MVC_Request',
			'TYPO3\FLOW3\Mvc\Controller\ArgumentError' => 'Tx_Extbase_MVC_Controller_ArgumentError',
			'TYPO3\FLOW3\Mvc\Controller\ControllerContext' => 'Tx_Extbase_MVC_Controller_ControllerContext',
			'TYPO3\FLOW3\Mvc\Web\Request' => 'Tx_Extbase_MVC_Web_Request',
			'TYPO3\FLOW3\Reflection\ReflectionService' => 'Tx_Extbase_Reflection_Service', // FIXME
			'TYPO3\FLOW3\Tests\UnitTestCase' => 'Tx_Extbase_Tests_Unit_BaseTestCase',
			'TYPO3\FLOW3\Validation\PropertyError' => 'Tx_Extbase_Validation_PropertyError',
			'TYPO3\FLOW3\Persistence\PersistenceManagerInterface' => 'Tx_Extbase_Persistence_ManagerInterface', // FIXME
			'__DIR__' => 'dirname(__FILE__)',
			'UUID' => 'UID',
			'\\vfsStream' => 'vfsStream',
			'\file_put_contents' => 'file_put_contents',
			'This script belongs to the FLOW3 package "TYPO3.Form".' => 'This script is backported from the FLOW3 package "TYPO3.Form".'
		);
		$this->backporter->setCodeProcessorClassName('TYPO3\Backporter\CodeProcessor\TestClassCodeProcessor');
		$this->backporter->setReplacePairs($replacePairs);
		$this->backporter->setIncludeFilePatterns(array(
			'#^Tests/Unit/.*Test.php$#',
		));
		$this->backporter->setExcludeFilePatterns(array(
			'#^Tests/View/.*$#',
			'#^Tests/Unit/ViewHelpers/SectionViewHelperTest.php$#',
			'#^Tests/Unit/ViewHelpers/Link/ActionViewHelperTest.php$#',
			'#^Tests/Unit/ViewHelpers/Link/EmailViewHelperTest.php$#',
			'#^Tests/Unit/ViewHelpers/Uri/ActionViewHelperTest.php$#',
			'#^Tests/Unit/ViewHelpers/Uri/ResourceViewHelperTest.php$#',
			'#^Tests/Unit/ViewHelpers/Uri/EmailViewHelperTest.php$#',
			'#^Tests/Unit/ViewHelpers/FormViewHelperTest.php$#', // TODO: maybe Backport aftr property mapper adjustments
			'#^Tests/Unit/ViewHelpers/Form/AbstractFormViewHelperTest.php$#', // Abstract Form ViewHelper works differently internally, as UUIDs are assigned on object creation time, vs. UIDs (v4) are assigned on persistence.
			'#^Tests/Unit/ViewHelpers/Format/CropViewHelperTest.php$#',
			'#^Tests/Unit/ViewHelpers/Form/ValidationResultsViewHelperTest.php$#', // TODO: Backport aftr property mapper adjustments
			'#^Tests/Unit/ViewHelpers/TranslateViewHelperTest.php$#',
			'#^Tests/Unit/ViewHelpers/Security/.*$#',
			'#^Tests/Unit/ViewHelpers/Identity/.*$#',
			'#^Tests/Unit/ViewHelpers/RenderChildrenViewHelperTest.php$#',
			'#^Tests/Unit/Core/Parser/Interceptor/ResourceTest.php$#',
			'#^Tests/Unit/Core/Widget/.*#'
		));
		$this->backporter->setFileSpecificReplacePairs(array(
			'Tests/Unit/ViewHelpers/ForViewHelperTest.php' => array(
				'$splObjectStorageObject->attach($object2, \'foo\');' => '$splObjectStorageObject->attach($object2);', // second parameter of SplObjectStorage::attach() seems to be available in PHP 5.3+ only
				'$splObjectStorageObject->offsetSet($object3, \'bar\');' => '$splObjectStorageObject->attach($object3);', // second parameter of SplObjectStorage::offsetSet() is available in PHP 5.3+ only
			),
			'Tests/Unit/View/TemplateViewTest.php' => array(
				'class TemplateViewTest' => '@require_once(\'vfsStream/vfsStream.php\'); // include vfs stream wrapper' . chr(10) . 'class TemplateViewTest'
			),
			'Tests/Unit/Core/Parser/TemplateParserPatternTest.php' => array(
				'Acme.MyPackage\\Bla\\' => 'Tx_AcmeMyPackage_Bla_',
				'Foo\\Bla3\\' => 'Tx_Foo_Bla3_',
				'TYPO3.Fluid\\Bla3\\' => 'Tx_Fluid_Bla3_',
				'TYPO3.TYPO3\\Bla3\\' => 'Tx_TYPO3_Bla3_'
			),
				// Mark tests as skipped
			'Tests/Unit/ViewHelpers/Form/AbstractFormFieldViewHelperTest.php' => array(
				'ifAnAttributeValueIsAnObjectMaintainedByThePersistenceManagerItIsConvertedToAUID() {'
				=> 'ifAnAttributeValueIsAnObjectMaintainedByThePersistenceManagerItIsConvertedToAUID() { $this->markTestIncomplete("TODO - fix test in backporter");'
			),
			'Tests/Unit/ViewHelpers/Form/SelectViewHelperTest.php' => array(
				'selectWithoutFurtherConfigurationOnDomainObjectsUsesUuidForValueAndLabel() {'
				=> 'selectWithoutFurtherConfigurationOnDomainObjectsUsesUuidForValueAndLabel() { $this->markTestIncomplete("TODO - fix test in backporter");',
				'selectWithoutFurtherConfigurationOnDomainObjectsUsesToStringForLabelIfAvailable() {'
				=> 'selectWithoutFurtherConfigurationOnDomainObjectsUsesToStringForLabelIfAvailable() { $this->markTestIncomplete("TODO - fix test in backporter");',
				'selectOnDomainObjectsCreatesExpectedOptions() {'
				=> 'selectOnDomainObjectsCreatesExpectedOptions() { $this->markTestIncomplete("TODO - fix test in backporter");',
				'selectOnDomainObjectsThrowsExceptionIfNoValueCanBeFound() {'
				=> 'selectOnDomainObjectsThrowsExceptionIfNoValueCanBeFound() { $this->markTestIncomplete("TODO - fix test in backporter");'
			)
		));
		$this->backporter->processFiles($this->packageManager->getPackage('TYPO3.Form')->getPackagePath(), $this->settings['targetPath']);
	}
	
	/**
	 * @return void 
	 */
	protected function processConfigurationFiles() {
		$this->backporter->emptyTargetPath(FALSE);
		$replacePairs = array(
			$this->getClassPrefix().'Validation_Validator_AlphanumericValidator' => 'Tx_Extbase_Validation_Validator_AlphanumericValidator',
			'TYPO3\FLOW3\Validation\Validator\AlphanumericValidator' => 'Tx_Extbase_Validation_Validator_AlphanumericValidator',
			'TYPO3\FLOW3\Validation\Validator\DateTimeRangeValidator' => 'Tx_Extbase_Validation_Validator_DateTimeValidator',
			'TYPO3\FLOW3\Validation\Validator\EmailAddressValidator' => 'Tx_Extbase_Validation_Validator_EmailAddressValidator',
			'TYPO3\FLOW3\Validation\Validator\FloatValidator' => 'Tx_Extbase_Validation_Validator_FloatValidator',
			'TYPO3\FLOW3\Validation\Validator\IntegerValidator' => 'Tx_Extbase_Validation_Validator_IntegerValidator',
			'TYPO3\FLOW3\Validation\Validator\NotEmptyValidator' => 'Tx_Extbase_Validation_Validator_NotEmptyValidator',
			'TYPO3\FLOW3\Validation\Validator\NumberRangeValidator' => 'Tx_Extbase_Validation_Validator_NumberRangeValidator',
			'TYPO3\FLOW3\Validation\Validator\RegularExpressionValidator' => 'Tx_Extbase_Validation_Validator_RegularExpressionValidator',
			'TYPO3\FLOW3\Validation\Validator\StringLengthValidator' => 'Tx_Extbase_Validation_Validator_StringLengthValidator',
			'TYPO3\FLOW3\Validation\Validator\TextValidator' => 'Tx_Extbase_Validation_Validator_TextValidator',
			'resource://{@package}' => 'EXT:'.$this->extensionKey.'/Resources'
		);
		$this->backporter->setReplacePairs($replacePairs);
		$this->backporter->setFileSpecificReplacePairs(array());
		$this->backporter->setCodeProcessorClassName('TYPO3\FormBackporter\CodeProcessor\ConfigurationCodeProcessor');
		$this->backporter->setIncludeFilePatterns(array(
			'#^Configuration/Settings.yaml$#',
		));
		$this->backporter->processFiles($this->packageManager->getPackage('TYPO3.Form')->getPackagePath(), $this->settings['targetPath']);
		
		if (!is_dir($this->settings['targetPath'].'Configuration/TypoScript/'))
			mkdir($this->settings['targetPath'].'Configuration/TypoScript/');
		rename($this->settings['targetPath'].'Configuration/Settings.yaml', $this->settings['targetPath'].'Configuration/TypoScript/setup.txt');
	}
	
	/**
	 * Processes the template files
	 * 
	 * @return void 
	 */
	protected function processTemplateFiles() {
		$this->backporter->emptyTargetPath(FALSE);
		$replacePairs = array(
			'f:form.checkbox'	=> 'form:form.checkbox'
		);
		$this->backporter->setReplacePairs($replacePairs);
		$this->backporter->setFileSpecificReplacePairs(array());
		$this->backporter->setCodeProcessorClassName('TYPO3\FormBackporter\CodeProcessor\TemplateCodeProcessor');
		$this->backporter->setIncludeFilePatterns(array(
			'#^Resources/Private/Form/.*.html$#',
		));
		$this->backporter->processFiles($this->packageManager->getPackage('TYPO3.Form')->getPackagePath(), $this->settings['targetPath']);
	}
	
	/**
	 * Creates class name replacement pairs
	 * 
	 * @param array $classNames
	 * @return array 
	 */
	protected function createClassNameReplacePairs(array $classNames) {
		$classNameReplacePairs = array();
		foreach ($classNames as $search => $replace) {
			$classNameReplacePairs = array_merge($classNameReplacePairs, array(
				'extends '.$search => 'extends '.$replace,
				'implements '.$search => 'implements '.$replace,
				'* @param '.$search => '* @param '.$replace,
				'* @return '.$search => '* @return '.$replace,
				'('.$search => '('.$replace,
				', '.$search => ', '.$replace,
				'instanceof '.$search => 'instanceof '.$replace,
				'new '.$search => 'new '.$replace
			));
		}
		return $classNameReplacePairs;
	}
	
}
?>