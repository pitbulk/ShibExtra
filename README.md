# ILIAS ShibExtra Plugin
An ILIAS Shibboleth Authentication extension for adding custom logs.

## Installation Instructions
1. Clone this repository to <ILIAS_DIRECTORY>/Customizing/global/plugins/Services/AuthShibboleth/ShibbolethAuthenticationHook/ShibExtra
2. Login to ILIAS with an administrator account (e.g. root)
3. Select **Plugins** from the **Administration** main menu drop down.
4. Search the **ShibExtra** plugin in the list of plugin and choose **Activate** from the **Actions** drop down.
5. Choose **Configure** from the **Actions** drop down and configure the plugin.

### General
There are 2 kind of file logs that will be stored at the 
`logs` folder inside the ilias data folder.
* saml_info.log Will log the actions (SSO, SLO, JIT)
* saml_debug_info.log ($_SERVER, Mapped user data, $shibUser)

### License

 This puglin is published under the General Public Licence and free of charge.
 It was sponsored by the "[Universidad de Jaén](http://www.ujaen.es/)".
