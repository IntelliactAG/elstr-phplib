<?php
/**
* Class to handle multi language strings
*
* @author Marco Egli
* @copyright 2009 Intelliact AG
*/
require_once ('ELSTR_Exception.php');

class ELSTR_Language {
    var $m_modules;
    var $m_translation;
    var $m_options;

    function __construct($options)
    {
        $this->m_options = $options;
        $this->m_modules = array();
        $this->m_session = new Zend_Session_Namespace('ELSTR_Language');

        $this->_loadInitialModule();
        $this->_loadRegisteredModules();
    }

    /**
    * Cleanup the registered modules and clear already loaded modules
    * Usually this method is called at every startup of an app
    *
    * @return void
    */
    public function cleanup()
    {
        // Unregister all modules
        // Delete all modules in the session
        $this->m_session->modules = array();
        // Delete all internal modules
        $this->m_modules = array();
        // Load the initial module
        $this->_loadInitialModule();
    }

    /**
    * Change the language in the session
    *
    * @param string $newLang
    * @return void
    */
    public function changeLanguage($lang)
    {
        if ($this->m_translation->isAvailable($lang)) {
            $this->m_session->language = $lang;
            $this->m_translation->setLocale($this->m_session->language);
        }
    }

    /**
	* Gets the current selected language from the session
	*
	* @return string $lang
	*/
    public function getCurrentLanguage()
	{
		return $this->m_session->language;
	}

    /**
    * Add Language Modules for use over session
    *
    * @param array $modules
    * @return array loaded modules
    */
    public function registerModules($modules)
    {
        $this->_addModules($modules, 'permanent');
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
        $this->_addModules($modules, 'temp');
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

    /**
    * Get the default language
    *
    * @return objecct
    */
    public function defaultLanguage() {
        return $this->m_options['default'];
    }

    /**
    * Get the language options for the data
    *
    * @return objecct
    */
    public function dataOptions() {
        $dataOptions = new stdClass();
        if(isset($this->m_options['data'])){
            $dataOptions = $this->m_options['data'];
        }
        return $dataOptions;
    }

    private function _addModules($modules, $type)
    {
        for ($i = 0; $i < count($modules); $i++) {
            $moduleName = $modules[$i];

            if (!isset($this->m_modules[$moduleName])) {
                $this->_loadModule($moduleName);

                if ($type == 'permanent') {
                    $this->m_session->modules[] = $moduleName;
                }
            }
        }
    }

    private function _loadModule($moduleName)
    {

    	if (isset($this->m_options['module'][$moduleName])) {
    		// It is a configurated module
			$filename = APPLICATION_PATH . $this->m_options['module'][$moduleName];
    	} else {
    		// It is not in the config so it must be an elstr module
    		$filename = APPLICATION_PATH . "/phplib/elstr/".ELSTR_VERSION."/translations/" . $moduleName . ".tmx";
    	}


        if (file_exists($filename)) {
            $this->m_translation->addTranslation($filename, $this->m_session->language);
            $this->m_modules[$moduleName] = true;
            return true;
        } else {
            // File does not exist
            return false;
        }
    }

    private function _loadInitialModule()
    {
        $filename = APPLICATION_PATH . $this->m_options['module']['default'];
        if (file_exists($filename)) {
            $this->m_translation = new Zend_Translate('tmx', $filename, $this->m_options['default']);

            if (!isset($this->m_session->language)) {
                $locale = new Zend_Locale();
                Zend_Registry::set('Zend_Locale', $locale);
                if (isset($this->m_options['forcedefault']) && $this->m_options['forcedefault'] == true) {
                    $this->m_session->language = $this->m_options['default'];
                } else if(count($locale->getBrowser()) === 0) {
                    // For all non browser requests
                    $this->m_session->language = $this->m_options['default'];
                } else {
                    if (!$this->m_translation->isAvailable($locale->getLanguage())) {
                        // when user reque$this->m_session->language = $this->m_options['default'];sts a not available language reroute to default
                        $this->m_session->language = $this->m_options['default'];
                    } else {
                        $this->m_session->language = $locale->getLanguage();
                    }
                }
            }
            $this->m_translation->setLocale($this->m_session->language);
            return true;
        } else {
        	throw new ELSTR_Exception('1010',1010,null,$this);
            return false;
        }
    }

    private function _loadRegisteredModules()
    {
        if (isset($this->m_session->modules) && is_array($this->m_session->modules)) {
            for ($i = 0; $i < count($this->m_session->modules); $i++) {
                $this->_loadModule($this->m_session->modules[$i]);
            }
        } else {
            $this->m_session->modules = array();
        }
    }
}

