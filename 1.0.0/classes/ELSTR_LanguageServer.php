<?php

require_once ('ELSTR_Language.php');
require_once ('ELSTR_JsonServer.php');

/**
 * Class to handle multi language strings
 *
 * @author Marco Egli
 * @copyright 2009 Intelliact AG
 */

class ELSTR_LanguageServer {
	private $m_application;

	function __construct($application)
	{
		$this->m_application = $application;
	}

	/**
	 * Create a JSON Server and handle itselfs
	 *
	 * @return void
	 */
	public function handle()
	{
		$server = new ELSTR_JsonServer();
		$server->setClass($this);
		$server->handle();
	}



    /**
     *
     * Funktion fur das Laden einer Sprache
     *
     * @param string $file
     * @param string $lang
     * @return array
     *
     */
    public function load($file, $lang) {

    	if ($file == "") {
    		$this->m_application->getBootstrap()->getResource("language")->changeLanguage($lang);
			$translations = $this->m_application->getBootstrap()->getResource("language")->getTranslation();
    	} else {
    		$translations = new Zend_Translate('tmx', $file, 'de');
    		$defaultlanguage = 'de';
    		// Pruefen, ob eine Uebersetzung exisiert
    		if ($translations->isAvailable($lang)) {
    			// Spracheinstellung der Session aendern
    			$_SESSION['language'] = $lang;
    		} else {
    			// Spracheinstellung der Session aendern
    			$_SESSION['language'] = $defaultlanguage;
    		}
    		// Spracheinstellung der Uebersetzungen (Objekte) aendern
    		$translations->setLocale($_SESSION['language']);
    	}


        // returns all the complete translation data
        return $translations->getMessages();
    }

	/**
	 *
	 * Funktion fur das Laden einer Sprache
	 *
	 * @param string $module
	 * @return array
	 *
	 */
	public function registerModule($module) {

		$this->m_application->getBootstrap()->getResource("language")->registerModules(array($module));

		// returns all the complete translation data
		return true;
	}


}


?>