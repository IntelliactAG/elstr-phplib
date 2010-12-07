<?php
require_once ('ELSTR_WidgetServer_Abstract.php');
require_once ('ELSTR_Response.php');
/**
* This is an abstract WidgetServer implementation which returns a binary stream
*
* @author Felix Nyffenegger
* @version 1.0
* @created 19-Nov-2009 17:41:37
*/

abstract class ELSTR_WidgetServer_Stream_Abstract extends ELSTR_WidgetServer_Abstract {
    protected $m_response;
    protected $m_paramArray;

    function __construct($acl = null, $user = null)
    {
        parent::__construct($acl, $user);
        $this->m_response = new ELSTR_Response();
        $this->m_paramArray = $this->_getParamArray();
    }

    /**
    */
    protected function setHeader($name, $value, $replace = false)
    {
        $this->m_response->setHeader ($name, $value, $replace);
    }

    /**
    * Removes and replaces special characters in filenames
    *
    * @param string $filename
    * @return string
    */
    protected function cleanFilename($filename)
    {
        $pattern = Array("/ä/", "/ö/", "/ü/", "/Ä/", "/Ö/", "/Ü/", "/ß/", "/é/", "/è/", "/à/", "/ç/");
        $replacement = Array("ae", "oe", "ue", "Ae", "Oe", "Ue", "ss", "e", "e", "a", "c");
        $filename = preg_replace($pattern, $replacement, $filename);

        $pattern = array("([^a-zA-Z0-9_ .-])", "(-{2,})");
        $replacement = array("-", "-");
        $filename = preg_replace($pattern, $replacement, $filename);

        return $filename;
    }

    /**
    *
    * @return void
    */
    protected function setDefaultContentTyeHeader($fileName)
    {
        $pathinfo = pathinfo($fileName);
        $extension = strtolower($pathinfo['extension']);

        switch ($extension) {
            case "pdf": $contentType = 'application/pdf';
                break;
            case "zip": $contentType = 'application/zip';
                break;
            case "doc": $contentType = 'application/force-download';
                break;
            case "xls": $contentType = 'application/force-download';
                break;
            case "ppt": $contentType = 'application/force-download';
                break;
            case "xml": $contentType = 'application/xml';
                break;
            case "exe": $contentType = 'application/force-download';
                break;
            case "slddrw": $contentType = 'application/force-download';
                break;
            case "dxf": $contentType = 'application/force-download';
                break;
            case "tif": $contentType = "image/tif";
                break;
            case "bmp": $contentType = "image/bmp";
                break;
            case "psd": $contentType = 'image/x-photoshop';
                break;
            case "hpgl": $contentType = 'image/x-hpgl';
                break;
            case "gif": $contentType = "image/gif";
                break;
            case "png": $contentType = 'image/png';
                break;
            case "jpeg":$contentType = 'image/jpg';
                break;
            case "jpg": $contentType = 'image/jpg';
                break;
            case "mp3": $contentType = 'audio/mpeg';
                break;
            case "wav": $contentType = 'audio/x-wav';
                break;
            case "mpeg":$contentType = 'video/mpeg';
                break;
            case "mpg": $contentType = 'video/mpeg';
                break;
            case "mpe": $contentType = 'video/mpeg';
                break;
            case "mov": $contentType = 'video/quicktime';
                break;
            case "avi": $contentType = 'video/x-msvideo';
                break;
            default: $contentType = 'text/html';
                break;
        }

        $this->setHeader('Content-Type', $contentType, true);
    }

    protected function _getParamArray(){
    	$paramArray = array();    
    	if ($_SERVER['REQUEST_METHOD'] == "GET"){
        	$paramArray = $_GET;
        } elseif ($_SERVER['REQUEST_METHOD'] == "POST"){
                $content = file_get_contents('php://input');
                $post = json_decode($content);
                $paramArray['method'] = $post->method;
                $paramArray = array_merge($paramArray, get_object_vars($post->params));
        }
        //var_dump($paramArray);
        return $paramArray;
    }
    
	/**
	 * Implementation of the abstract _getMethod
	 */
	protected function _getMethod() {
		return $this->m_paramArray['method'];
	}
	
    /**
    * Create a Response and handle itselfs
    *
    * @return void
    */
    protected function _handle()
    {
        $callmethod = $this->_getMethod();

        $this->m_response->appendBody($this->$callmethod($this->m_paramArray));
        $this->m_response->sendResponse();
    }
}

?>