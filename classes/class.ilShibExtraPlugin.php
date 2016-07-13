<?php

require_once 'Services/AuthShibboleth/classes/class.ilShibbolethAuthenticationPlugin.php';

class ilShibExtraPlugin extends ilShibbolethAuthenticationPlugin {

	const LOG_FILE = 'saml_info.log';
	const DEBUG_LOG_FILE = 'saml_debug_info.log';
	const DATEFORMAT = 'y-m-d H:i:s';

	// https://github.com/ILIAS-eLearning/ILIAS/blob/769b63095c1d4a40e23ae269e90e0e853bb734c2/Services/Logging/classes/class.ilLog.php

	protected $log_file_handler;

	protected $settings;

	/**
	 * @return string
	 */
	final public function getPluginName()
	{
		return "ShibExtra";
	}

	/**
	 * @return ilShibExtraConfig
	 */
	protected function getSettings()
	{
		if(!$this->settings) {
			$this->includeClass("class.ilShibExtraConfig.php");
			$this->settings = new ilShibExtraConfig();
		}

		return $this->settings;
	}

	protected function addToLog($action, $user, $message="", $extraData="") {
		$filename = ILIAS_LOG_DIR . '/' . ilShibExtraPlugin::LOG_FILE;
		$this->writeLog($filename, $action, $user, $message, $extraData);
	}

	protected function addToDebugLog($action, $user=null, $message="", $extraData="") {
		$filename = ILIAS_LOG_DIR . '/' . ilShibExtraPlugin::DEBUG_LOG_FILE;
		$this->writeLog($filename, $action, $user, $message, $extraData);
	}

	protected function writeLog($filename, $action, $user, $message, $extraData) {
		$userStr = "";
		if (isset($user)) {
			$settings = $this->getSettings();
			$userDataMode = $settings->getUserDataMode();

			if ($userDataMode == 'username' && !empty($user->login)) {
				$userStr = $user->login;
			} else if ($userDataMode == 'email' && !empty($user->login)) {
				$userStr = $user->email;
			} else if ($userDataMode == 'username_and_email') {
				$userStr = "";
				if (!empty($user->login)) {
					$userStr = $user->login;
				}
				if (!empty($user->email)) {
					$userStr .= " | ".$user->email;
				}
			}
		}

		$line = "[ ".$_SERVER['REMOTE_ADDR']." ] " ."[ ". date(ilShibExtraPlugin::DATEFORMAT)." ] [ ".$action." ]"." [ ".$userStr." ] ";
		if (!empty($message)) {
			$line .= " [ ".$message." ]";
		}

		if (!empty($extraData)) {
			$line .= " [ ".$extraData." ]";
		}
		$line .= PHP_EOL;

		file_put_contents($filename, $line, FILE_APPEND);
	}

	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function beforeLogin(ilObjUser $user) {
		$settings = $this->getSettings();
		if ($settings->getDebugActive()) {
			$serverData = $this->returnServerData();
			$this->addToDebugLog("DEBUG", $user, '$_SERVER', json_encode($serverData));

			$shibData = $this->returnShibData();
			$this->addToDebugLog("DEBUG", $user, "SHIBDATA", json_encode($shibData));

			$userData = $this->returnUserData($user);
			$this->addToDebugLog("DEBUG", $user, "SHIBUSER", json_encode($userData));
		}
		return $user;
	}

	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function beforeUpdateUser(ilObjUser $user) {
		return $user;
	}

	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function beforeCreateUser(ilObjUser $user) {
		return $user;
	}

	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function afterLogin(ilObjUser $user) {
		$settings = $this->getSettings();
		$enabledActions = $settings->getLogActions();
		if (in_array('sso', $enabledActions)) {
			$this->addToLog("SSO", $user, "Success");
		}

		// Store user's Shibboleth sessionID for logout
	    $this->session['shibboleth_session_id'] = $_SERVER['Shib-Session-ID'];

	    // we are authenticated: redirect, if possible
	    if (isset($_GET["target"]) && !empty($_GET["target"])) {
			ilUtil::redirect("goto.php?target=".$_GET["target"]."&client_id=".CLIENT_ID);
	    }
		return $user;
	}

	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function afterCreateUser(ilObjUser $user) {
		$settings = $this->getSettings();
		$enabledActions = $settings->getLogActions();
		if (in_array('jit', $enabledActions)) {
			$this->addToLog("JIT", $user, "Success");
		}
		return $user;
	}

	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function beforeLogout(ilObjUser $user) {
		return $user;
	}

	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function afterLogout(ilObjUser $user) {
		$settings = $this->getSettings();
		$enabledActions = $settings->getLogActions();
		if (in_array('slo', $enabledActions)) {
			$this->addToLog("SLO", $user, "Success");
		}
		return $user;
	}

	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function afterUpdateUser(ilObjUser $user) {
		return $user;
	}

	protected function returnUserData($user) {

		$iliasUserData = array(
			'shib_login' => $user->login,
			'shib_title' => $user->title,
			'shib_firstname' => $user->firstname,
			'shib_lastname' => $user->lastname,
			'shib_email' => $user->email,
			'shib_gender' => $user->gender,
			'shib_institution' => $user->institution,
			'shib_department' => $user->department,
			'shib_zipcode' => $user->zipcode,
			'shib_city' => $user->city,
			'shib_country' => $user->country,
			'shib_street' => $user->street,
			'shib_phone_office' => $user->phone_office,
			'shib_phone_home' => $user->phone_home,
			'shib_phone_mobile' => $user->phone_mobile,
			'shib_language' => $user->language,
			'shib_matriculation' => $user->matriculation,
		);

		return $iliasUserData;
	}

	protected function returnShibData() {
		global $ilias;

		$shibDataArray = array();

		$iliasUserAttributes = array(
			'shib_login',
			'shib_title',
			'shib_firstname',
			'shib_lastname',
			'shib_email',
			'shib_gender',
			'shib_institution',
			'shib_department',
			'shib_zipcode',
			'shib_city',
			'shib_country',
			'shib_street',
			'shib_phone_office',
			'shib_phone_home',
			'shib_phone_mobile',
			'shib_language',
			'shib_matriculation',
		);

		foreach($iliasUserAttributes as $iliasUserAttribute) {
			$mapped = $ilias->getSetting($iliasUserAttribute);
			if (isset($_SERVER[$mapped])) {
				$shibDataArray[$iliasUserAttribute] = $_SERVER[$mapped];
			} else {
				$shibDataArray[$iliasUserAttribute] = "";
			}
		}

		return $shibDataArray;
	}

	protected function returnServerData()
	{
		$shibDataArray = array();
		$excludeArray = array(
			'argv',
			'argc',
			'AUTH_TYPE',			
			'CONTENT_LENGTH',
			'CONTENT_TYPE',
			'CONTEXT_DOCUMENT_ROOT',
			'CONTEXT_PREFIX',
			'DOCUMENT_ROOT',
			'GATEWAY_INTERFACE',
			'HTTP_ACCEPT',
			'HTTP_ACCEPT_CHARSET',
			'HTTP_ACCEPT_ENCODING',
			'HTTP_ACCEPT_LANGUAGE',
			'HTTP_CACHE_CONTROL',
			'HTTP_CONNECTION',
			'HTTP_COOKIE',
			'HTTP_HOST',
			'HTTP_REFERER',
			'HTTP_ORIGIN',
			'HTTP_USER_AGENT',
			'HTTP_UPGRADE_INSECURE_REQUESTS',
			'HTTPS',
			'PATH',
			'PATH_INFO',
			'PATH_TRANSLATED',
			'PHP_AUTH_DIGEST',
			'PHP_AUTH_USER',
			'PHP_AUTH_PW',
			'PHP_SELF',
			'QUERY_STRING',
			'REDIRECT_REMOTE_USER',
			'REMOTE_ADDR',
			'REMOTE_HOST',
			'REMOTE_PORT',
			'REMOTE_USER',
			'REQUEST_METHOD',
			'REQUEST_TIME',
			'REQUEST_TIME_FLOAT',
			'REQUEST_SCHEME',
			'REQUEST_URI',
			'SCRIPT_FILENAME',
			'SCRIPT_NAME',
			'SCRIPT_URL',
			'SERVER_ADDR',
			'SERVER_ADMIN',
			'SERVER_NAME',
			'SERVER_PROTOCOL',
			'SERVER_PORT',
			'SERVER_SIGNATURE',
			'SERVER_SOFTWARE',
			'ORIG_PATH_INFO'
		);

		foreach(array_keys($_SERVER) as $key) {
			if (!in_array($key, $excludeArray)) {
				$shibDataArray[$key] = $_SERVER[$key];
			}
		}

		return $shibDataArray;
	}

	/**
	 * uninstall plugin data
	 */
	protected function afterUninstall()
	{
		$settings = new ilSetting("shibextra");

		$settings->deleteAll();
	}
}

?>
