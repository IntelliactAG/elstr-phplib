<?php
require_once ('ELSTR_WidgetServer_Abstract.php');

/**
* This is an abstract WidgetServer implementation which returns a binary stream
*
* @author Marco Egli
* @version 1.0
* @created 24-Feb-2010 17:41:37
*/

abstract class ELSTR_FeedServer_RSS_Abstract extends ELSTR_WidgetServer_Abstract {

	function __construct($acl = null, $user = null) {
        parent::__construct($acl, $user);
    }

    protected function _getMethod() {
    	return "_getFeedArray";
    }

    /**
    * Get Feed Array
    * The implementation class must implement this method 
    * Array specification at http://framework.zend.com/manual/en/zend.feed.importing.html
    *
    * @param array $paramArray
    * @return array 
    */
	abstract protected function _getFeedArray($paramArray);
    
    /**
    * Create a Response and handle itselfs
    *
    * @return void
    */
    protected function _handle()
    {
        $callmethod = $this->_getMethod();
        $paramArray = $_GET;
 
		$rssFeedFromArray = Zend_Feed::importArray($this->$callmethod($paramArray), 'rss');
        // send http headers and dump the feed
        $rssFeedFromArray->send();
    }
}

?>