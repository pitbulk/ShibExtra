<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
 
class ilShibExtraConfigGUI extends ilPluginConfigGUI
{
	/**
	 * @var ilShibExtraConfigGUI
	 */
	protected $config;

	/**
	* Handles all commmands, default is "configure"
	*/
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "configure":
			case "save":
				$this->$cmd();
				break;
		}
	}

	/**
	 * Configure screen
	 */
	function configure()
	{
		global $tpl;

		$form = $this->initConfigurationForm();
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Init configuration form.
	 *
	 * @return ilPropertyFormGUI form object
	 */
	public function initConfigurationForm()
	{
		global $lng, $ilCtrl, $ilUser;
		$this->initConfig();
		$pl = $this->getPluginObject();
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->addCommandButton("save", $lng->txt("save"));

		$sec = new ilFormSectionHeaderGUI();
		$sec->setTitle($pl->txt("log"));
		$form->addItem($sec);

		$debug = new ilCheckboxInputGUI($pl->txt("debug"), "shibextra_debug");
		$debug->setInfo($pl->txt('debug_info'));
		$debug->setChecked($this->config->getDebugActive());
		$form->addItem($debug);

		$logActions = new ilCheckboxGroupInputGUI($pl->txt('log_actions'), "shibextra_log_actions");
		
		$ssoOption = new ilCheckboxOption($pl->txt("sso"), 'sso');
		$ssoOption->setInfo($pl->txt('sso_info'));
		$logActions->addOption($ssoOption);

		$sloOption = new ilCheckboxOption($pl->txt("slo"), 'slo');
		$sloOption->setInfo($pl->txt('slo_info'));
		$logActions->addOption($sloOption);

		$jitOption = new ilCheckboxOption($pl->txt("jit"), 'jit');
		$jitOption->setInfo($pl->txt('jit_info'));
		$logActions->addOption($jitOption);

		$logActionsValues = $this->config->getLogActions();
		$logActions->setValue($logActionsValues);
        $form->addItem($logActions);

		$userDataMode = new ilRadioGroupInputGUI($pl->txt('userdata_mode'), "shibextra_userdata");
		$userDataMode->setInfo($pl->txt('userdata_mode_info'));

        $usernameOpt = new ilRadioOption(
                $pl->txt('username_option'),
                'username'
        );
        $usernameOpt->setInfo($pl->txt('username_option_info'));
        $userDataMode->addOption($usernameOpt);

        $emailOpt = new ilRadioOption(
                $pl->txt('email_option'),
                'email'
        );
        $emailOpt->setInfo($pl->txt('email_option_info'));
        $userDataMode->addOption($emailOpt);

        $usernameAndEmailOpt = new ilRadioOption(
                $pl->txt('username_and_email_option'),
                'username_and_email'
        );
        $usernameAndEmailOpt->setInfo($pl->txt('username_and_email_option_info'));
        $userDataMode->addOption($usernameAndEmailOpt);

		$userDataMode->setValue($this->config->getUserDataMode());
        $form->addItem($userDataMode);

		$form->setFormAction($ilCtrl->getFormAction($this));

		return $form;
	}
	
	/**
	 * Save form input (currently does not save anything to db)
	 *
	 */
	public function save()
	{
		global $tpl, $lng, $ilCtrl;

		$this->initConfig();
		$pl = $this->getPluginObject();
		
		$form = $this->initConfigurationForm();
		if ($form->checkInput()) {
			$this->config->setDebugActive((bool)$_POST["shibextra_debug"]);
			$this->config->setLogActions($_POST["shibextra_log_actions"]);
			$this->config->setUserDataMode($_POST["shibextra_userdata"]);

			$this->config->update();

			ilUtil::sendSuccess($lng->txt("saved_successfully"), true);
			$ilCtrl->redirect($this, "configure");
		} else {
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}

	public function initConfig()
	{
		$this->getPluginObject()->includeClass("class.ilShibExtraConfig.php");

		$this->config = new ilShibExtraConfig();
	}
}
?>
