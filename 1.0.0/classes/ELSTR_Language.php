<?php
/**
* Class to handle multi language strings
*
* @author Marco Egli
* @copyright 2009 Intelliact AG
*/

class ELSTR_Language {
    var $m_modules;
    var $m_translations;
    var $m_options;

    function __construct($options)
    {
        $this->m_options = $options;
        $this->m_modules = array();
        $this->m_session = new Zend_Session_Namespace('ELSTR_Language');

        $this->m_translation = new Zend_Translate('tmx', APPLICATION_PATH . $this->m_options['module']['default'], $this->m_options['default']);

        if (!isset($this->m_session->language)) {
            $locale = new Zend_Locale();
            Zend_Registry::set('Zend_Locale', $locale);
            if (!$this->m_translation->isAvailable($locale->getLanguage())) {
                // when user requests a not available language reroute to default
                $this->m_session->language = $this->m_options['default'];
            } else {
                $this->m_session->language = $locale->getLanguage();
            }
        }
        $this->m_translation->setLocale($this->m_session->language);

        if (isset($this->m_session->modules) && is_array($this->m_session->modules)) {
            for ($i = 0; $i < count($this->m_session->modules); $i++) {
                $this->loadModule($this->m_session->modules[$i]);
            }
        } else {
            $this->m_session->modules = array();
        }
    }

	/**
	 * Change the language in the session
	 *
	 * @param string $newLang
	 * @return void
	 */
	public function changeLanguage($lang){
		if ($this->m_translation->isAvailable($lang)) {
			$this->m_session->language = $lang;
			$this->m_translation->setLocale($this->m_session->language);
		}
	}


    /**
    * Add Language Modules for use over session
    *
    * @param array $modules
    * @return array loaded modules
    */
    public function registerModules($modules)
    {
        $this->addModules($modules, 'permanent');
        return $this->m_modules;
    }

    /**
    * Add Language Modules for use only for this request
    *
    * @param array $modules
    * @return array loaded modules
    */
    public function useModules($modules)
    {
        $this->addModules($modules, 'temp');
        return $this->m_modules;
    }

    /**
    * Returns the loaded Modules
    *
    * @return array loaded modules
    */
    public function getRegisteredModules()
    {
        return $this->m_session->modules;
    }

    /**
    * Get the Zend Tranlation Objedct
    *
    * @return objecct
    */
    public function getTranslation()
    {
        return $this->m_translation;
    }

    private function addModules($modules, $type)
    {
        for ($i = 0; $i < count($modules); $i++) {
            $moduleName = $modules[$i];

            if (!isset($this->m_modules[$moduleName])) {
                $this->loadModule($moduleName);

                if ($type == 'permanent') {
                    $this->m_session->modules[] = $moduleName;
                }
            }
        }
    }

    private function loadModule($moduleName)
    {
        $this->m_translation->addTranslation(APPLICATION_PATH . $this->m_options['module'][$moduleName], $this->m_session->language);
        $this->m_modules[$moduleName] = true;
    }
}

?>