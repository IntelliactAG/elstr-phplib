<?php 
/**
 * Class to handle multi language strings
 * 
 * @author Marco Egli
 * @copyright 2009 Intelliact AG
 */
 
class ELSTR_LanguageServer {
    /**
     *
     * Funktion fur das Laden einer Sprache
     *
     * @param string $file
     * @param string $lang
     * @return array
     *
     */
    public function get($file, $lang) {
    
        $textTranslations = new Zend_Translate('tmx', $file, 'de');
        $defaultlanguage = 'de';
        // Pruefen, ob eine Uebersetzung exisiert
        if ($textTranslations->isAvailable($lang)) {
            // Spracheinstellung der Session aendern
            $_SESSION['language'] = $lang;
        } else {
            // Spracheinstellung der Session aendern
            $_SESSION['language'] = $defaultlanguage;
        }
        // Spracheinstellung der Uebersetzungen (Objekte) aendern
        $textTranslations->setLocale($_SESSION['language']);
        
        // returns all the complete translation data
        return $textTranslations->getMessages();
    }
}


?>
