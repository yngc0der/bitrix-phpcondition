<?php
/**
 * @author RG. <rg.archuser@gmail.com>
 */


use Bitrix\Main\LoaderException;
use Bitrix\Main\Context;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Config\Option;


Loc::loadMessages(__FILE__);

class yngc0der_phpcondition extends \CModule
{
    protected $cli_mode;

	public function __construct($cli_mode = false)
	{
	    $this->cli_mode = $cli_mode;

		$arModuleVersion = [];
		include(__DIR__ . '/version.php');

		$this->MODULE_ID = 'yngc0der.phpcondition';
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->MODULE_NAME = Loc::getMessage('YC_PHP_COND_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('YC_PHP_COND_MODULE_DESC');

		$this->PARTNER_NAME = Loc::getMessage('YC_PHP_COND_PARTNER_NAME');
		$this->PARTNER_URI = Loc::getMessage('YC_PHP_COND_PARTNER_URI');
	}

	public function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler(
            'sale',
            'onBuildDiscountConditionInterfaceControls',
            $this->MODULE_ID,
            '\\Yngc0der\\PhpCondition\\PhpConditionControl',
            'onBuildDiscountConditionInterfaceControls'
        );
    }

    public function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'sale',
            'onBuildDiscountConditionInterfaceControls',
            $this->MODULE_ID,
            '\\Yngc0der\\PhpCondition\\PhpConditionControl',
            'onBuildDiscountConditionInterfaceControls'
        );
    }

    public function DoInstall()
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

        if (!$this->isVersionD7()) {
            throw new LoaderException(Loc::getMessage('YC_PHP_COND_ERROR_NOT_D7_CORE'));
        }

        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallEvents();

        if (!$this->cli_mode) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('YC_MODULE_INSTALL_TITLE'),
                $this->getPath() . '/install/step.php'
            );
        }
	}

    public function DoUninstall()
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		$request = Context::getCurrent()->getRequest();

		if ($request->get('uninstall') != 'Y') {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('YC_PHP_COND_UNINSTALL_TITLE'),
                $this->GetPath() . '/install/unstep.php'
            );

            return;
        }

        $this->UnInstallEvents();
        Option::delete($this->MODULE_ID);
        ModuleManager::unRegisterModule($this->MODULE_ID);
	}

    public function isVersionD7()
	{
		return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
	}

    public function getPath($notDocumentRoot = false)
	{
	    return $notDocumentRoot
            ? str_ireplace(Application::getDocumentRoot(),'', dirname(__DIR__))
            : dirname(__DIR__);
	}
}
