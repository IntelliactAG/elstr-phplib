<?php

require_once ('ELSTR_Exception.php');

/**
 * ElstrExceptionHandler Utilities
 *
 *  display_startup_errors=off
 *  display_errors=off
 *  html_errors=off
 *  docref_root=0
 *  docref_ext=0
 *
 *
 * @version 0.1
 * @copyright 2015
 */


function elstrExceptionGetMessages($exception){

    $messages = array();

    if ($exception instanceof ELSTR_Exception){

        syslog(LOG_NOTICE,print_r($exception,true));

        $header = $exception->getHeader();
        header($header);

        $resp = $exception->getResponse();

        $message = $resp['error']['message'];
        $code = $resp['error']['code'];

        $messages[] = array("type" => "error",
            "code" => $code,
            "message" => $message);

    } else if ($exception instanceof Zend_Exception){

        syslog(LOG_NOTICE,print_r($exception,true));

        $header = $exception->getHeader();
        header($header);

        $message = $exception->getMessage();

        $messages[] = array("type" => "error",
            "code" => "0000",
            "message" => $message);

    }else{

        syslog(LOG_ERR,print_r($exception,true));

        $message = $exception->getMessage();

        if (isset($exception->sqlMessage)){
            $message .= $exception->sqlMessage;
        }

        $messages[] = array("type" => "error",
            "code" => "0000",
            "message" => "Unexpected Exception: ".$message,
            "exception" => $exception->getFile()." Line: ".$exception->getLine(), // we do not know the ELSTER log here, send exception info to the browser so that it can be viewed with the browser debugging tools
            "trace" => $exception->getTrace()
        );
    }

    return $messages;

}


function elstrProcessException($exception){

    $results = array();
    $data = array();


    $results['data'] = $data;
    $results['messages'] = elstrExceptionGetMessages($exception);

    return $results;
}

function ElstrExceptionHandler($exception) {

    $results = elstrProcessException($exception);

    $result = array();
    $result['result'] = $results;

    header('Content-Type: application/json');
    echo json_encode($result);

}

function exception_error_handler($errno, $errstr, $errfile, $errline ) {

    throw new ELSTR_Exception($errstr ." IN ". $errfile." Line (". $errline.")");
    exit();

}

set_exception_handler('ElstrExceptionHandler');
set_error_handler("exception_error_handler");
