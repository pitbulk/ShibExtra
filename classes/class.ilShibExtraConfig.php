<?php
 
class ilShibExtraConfig
{
	/**
	 * @var ilSetting
	 */
	protected $setting;

	/**
	 * @var bool
	 */
	protected $debug = false;

	/**
	 * @var array
	 */
	protected $logActions = array();

	/**
	 * @var string userDataMode
	 */
	protected $userDataMode = 'username';


	public function __construct()
	{
		$this->setting = new ilSetting("shibextra");
		$this->read();
	}

	public function read()
	{
		$this->setting->read();

		$this->setDebugActive((bool)$this->setting->get("shibextra_debug", $this->getDebugActive()));
		$this->setLogActions($this->setting->get("shibextra_log_actions", $this->getLogActions()));
		$this->setUserDataMode($this->setting->get("shibextra_userdata", $this->getUserDataMode()));
	}

	public function update()
	{
		$this->setting->set("shibextra_debug", (bool) $this->getDebugActive());
		$this->setting->set("shibextra_log_actions", $this->getLogActions());
		$this->setting->set("shibextra_userdata", $this->getUserDataMode());
	}

	/**
	 * @return bool
	 */
	public function getDebugActive()
	{
		return $this->debug;
	}

	/**
	 * @param array $bool
	 */
	public function setDebugActive($debug)
	{
		$this->debug = $debug;
	}

	/**
	 * @return array
	 */
	/*
	public function getLogActionsValues()
	{
		return unserialize($this->logActions);
	}
	*/

	/**
	 * @return string
	 */
	public function getLogActions()
	{
		if (is_string($this->logActions)) {
			$values	= unserialize($this->logActions);
		} else {
			$values = $this->logActions;
		}
		return $values;
	}

	/**
	 * @param string/array $userDataMode
	 */
	public function setLogActions($logActions)
	{
		if (is_string($logActions)) {
			$values	= unserialize($logActions);
		} else {
			$values = $logActions;
		}
		$this->logActions = $values;
 	}

	/**
	 * @return string
	 */
	public function getUserDataMode()
	{
		return $this->userDataMode;
	}

	/**
	 * @param string/Array $userDataMode
	 */
	public function setUserDataMode($userDataMode)
	{
		$this->userDataMode = $userDataMode;
	}
}
?>