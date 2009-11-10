<?php 
require_once ('ELSTR_Service_Abstract.php');

/**
 * This as an example of a custom application, using http authentication
 *
 * @author Marco Egli
 */
class ELSTR_Service_OracleE6 extends ELSTR_Service_Abstract {

    private $gRemoteAddress;
    private $gSessionID;
    private $gTimeoutSeconds;
    private $gThisServer;
    private $gFileCache;
    
    /**
     *
     * @return
     */
    function __construct() {
        parent::__construct();
        
        $this->gRemoteAddress = "tcp://vm-0027:9010";
        $this->gSessionID = "1000004244";
        $this->gTimeoutSeconds = "60.0";
        $this->gThisServer = "webServer";
        $this->gFileCache = array();
    }

    
    // Invokes an axalant procedure via IctConnector
    // $argArray: array containing function name and arguments for IctConnector
    // dies on error
    protected function invokeConnectorProcedure($plmFunction, $plmParameters) {
    
        $argArray = array($plmFunction,
                 $this->gSessionID);
        
        array_push($argArray, $plmParameters);
        
        //$this->gSessionID = $argArray[1];
        $lastStatus = "noStatus";
        $timeLimit = time() + $this->gTimeoutSeconds;
        //echo "invokeConnectorProcedure: $argArray<BR>"; // for debugging only
        
        $httpGet = $this->encodeHttpGet($argArray);
        //echo "invokeConnectorProcedure '$httpGet'<BR>"; // for debugging only
        
        // send request to connector
        $data = $this->getHttpResponse($httpGet);
        if ($data[0] < 0) {
            $this->showFatalError("Error", $data[1], $data[0]);
        }
        // request could be handled by connector directly
        if (($data[0] == 200) || ($data[0] == 2000)) {
            return $data[1];
        }
        $gRequestID = $data[1];
        //echo "invokeConnectorProcedure requestID: $gRequestID<BR>"; // for debugging only
        // wait for complete status
        do {
            if (time() > $timeLimit) {
                echo "<H3>Request timed out</H3>Connector is responding but request $gRequestID in session $this->gSessionID was not handled within $this->gTimeoutSeconds  seconds time limit, last status:$lastStatus.<BR>Check PLM Server!<br>";
                exit();
            }
            $answer = $this->getConnectorStatus($gRequestID);
            $status = $answer[0];
            if ($status < 0) {
                showFatalError("Error", $answer[1], $status);
            }
            //echo "status: $status"; // for debugging only
            $lastStatus = $status;
        } while (($status < 2000) && ($status != 200)); //end do
        
        $xmlData = $this->getFileFromConnector($gRequestID);
        
        $resultArray = (Array ) new SimpleXMLElement($xmlData);
        
        return $resultArray;
    }
    
    /**      
     * Encodes a http GET header
     * @param argArray: array containing function name and arguments for IctConnector
     * dies on error
     */
    private function encodeHttpGet($argArray) {
    
        if (count($argArray) < 1) {
            showFatalError("PHP error", "Invalid function call encodeUrl($argArray)", -7010);
        }
        $serverUrl = rawurlencode($this->gThisServer);
        $url = "GET /$argArray[0]&$serverUrl";
        for ($i = 1; $i < count($argArray); $i = $i + 1) {
            $argUrl = rawurlencode($argArray[$i]);
            $url = $url."&".$argUrl;
        }
        $url = $url."\n\n";
        return $url;
    }
    
    /**
     * Send data to connector and interprete answer
     */
    private function getHttpResponse($request) {
    
        $fp = stream_socket_client($this->gRemoteAddress, $errno, $errstr, 4);
        if (!$fp) {
            return array(-7000,
                     "Could not connect to Intelliact Connector");
        }
        
        $contentHeader = $this->getHttpResponseHeader($request, $fp);
        $statusCode = $contentHeader[0];
        $statusText = $contentHeader[1];
        $contentLength = $contentHeader[2];
        $contentType = $contentHeader[3];
        
        $content = $this->getHttpResponseContent($statusCode, $contentLength, $fp);
        
        $statusCode = $content[0];
        $data = $content[1];
        fclose($fp);
        
        //echo "header: $header<BR><BR>";// for debugging only
        //displayHttpInfo(array($statusCode,$data,$contentType,$statusText)); // for debugging only
        
        // saveDataToFile($data,'result.txt'); // for debugging only
        //$completeMessage=$header..data; // for debugging only
        //saveDataToFile($completeMessage,'receivedMessage.txt'); // for debugging only
        
        //echo "SND: $request, RCV: numChar:$numChar, code:$responseCode, data:$data<br>";// for debugging only
        
        return array($statusCode,
                 $data,
                 $contentType,
                 $statusText);
    }

    
    /**
     * Get status for $requestID at IctConnector server
     * @param object $requestID
     * @return
     */
    private function getConnectorStatus($requestID) {
    
        $httpGet = $this->encodeHttpGet(array("getStatus", $this->gSessionID, $requestID));
        //echo "getConnectorStatus '$httpGet'<BR>"; // for debugging only
        
        $answer = $this->getHttpResponse($httpGet);
        //displayHttpInfo($answer); // for debugging only
        if ($answer[0] < 0) {
            showFatalError("Error", $answer[1], $answer[0]);
        }
        
        return $answer;
        
    }
    
    /**
     *
     * @param object $title
     * @param object $message
     * @param object $errorCode
     * @return
     */
    private function showFatalError($title, $message, $errorCode) {
        echo "<H3>$title</H3>$message<br><br>Error code: $errorCode<br>";
        exit();
    }
    
    /**
     *
     * @param object $request
     * @param object $fp
     * @return
     */
    private function getHttpResponseHeader($request, $fp) {
        $contentLength = 0;
        $statusCode = -7100; //default
        
        $this->saveDataToFile($request, 'sentMessage.bin'); // for debugging only
        
        $numChar = strlen($request);
        $numWrittten = fwrite($fp, $request, $numChar);
        
        if ($numWrittten === false) {
            fclose($fp);
            return array(-7001,
                     "Could not talk to Intelliact Connector");
        } else if ($numWrittten < $numChar) {
            fclose($fp);
            return array(-7002,
                     "Could not talk to Intelliact Connector");
        }
        fflush($fp);
        
        if (!feof($fp)) {
            $numChar = 0;
            $header = ""; //default
            $data = ""; //default
            $lastChar = 0;
            $iLoop = 0;
            
            // read header
            while (!feof($fp)) {
                $dataNew = fgetc($fp);
                /*if ($dataNew==false)
                 {
                 echo("header terminated<BR>");
                 break;
                 }*/
                if (strlen($dataNew) == 0) {
                    //echo("header strlen0<BR>");
                    break;
                }
                $i1 = ord($dataNew);
                $i2 = ord($lastChar);
                //echo "dataNew:$dataNew,i1:$i1,i2:$i2<BR>";
                if ((ord($dataNew) == 10) && (ord($lastChar) == 10)) {
                    //echo("header end<BR>");// for debugging only
                    break;
                }
                if (ord($dataNew) != 13) {
                    $lastChar = $dataNew;
                }
                $header = $header.$dataNew;
                if (strlen($header) == 4) {
                    if ($header != "HTTP") {
                        return array(-7004,
                                 "Received Invalid response header $header");
                    }
                }
                $iLoop = $iLoop + 1;
            }
            
            $contentType = $this->getHttpValue($header, "Content-Type:");
            
            $http = $this->getHttpValue($header, "HTTP");
            $statusCodeStr = trim(substr($http, 4));
            $iBlank = strpos($statusCodeStr, ' ');
            $statusCode = trim(substr($statusCodeStr, 0, $iBlank));
            $statusText = trim(substr($statusCodeStr, $iBlank));
            
            $contentLength = $this->getHttpValue($header, "Content-Length:");
            //echo "$contentLength1:$contentLength<BR>";// for debugging only
            $contentLength = (int) $contentLength;
            
            //saveDataToFile("contentLength: $contentLength",IctConnectorDebug1.txt");
            
        }
        return array($statusCode,
                 $statusText,
                 $contentLength,
                 $contentType);
    }

    
    /**
     * get HttpResponseContent and immediately send it to browser
     * @param object $statusCode
     * @param object $contentLength
     * @param object $fp
     * @return
     */
    private function getProgressiveHttpResponseContent($statusCode, $contentLength, $fp) {
        if (!$fp) { // this should never occur
            return array(-7000,
                     "Could not access content from Intelliact Connector");
        }
        $numReceived = 0;
        $data = ""; //default
        
        if (!feof($fp)) {
            $numCharTotal = 0;
            
            //saveDataToFile("before while","IctConnectorDebug4.txt");
            while (!feof($fp)) {
                $numMissing = $contentLength - $numReceived;
                if ($numMissing <= 0) {
                    break;
                }
                if ($numMissing + 1 > 1024) {
                    $newdata = fgets($fp, 1024); // for unknown reasons we must not get too many characters at once
                } else {
                    $newdata = fgets($fp, $numMissing + 1); // for unknown reasons we must get one more char
                }
                $data = $data.$newdata;
                // submitting to browser in medium sized blocks, gives better performance?
                if (strlen($data) > 9000) {
                    echo($data);
                    $data = "";
                }
                $numReceivedNew = strlen($newdata);
                $numReceived += $numReceivedNew;
                //saveDataToFile("numReceivedNew: $numReceivedNew, numReceived: $numReceived","IctConnectorDebug5.txt");
            }
            if (strlen($data) > 0) {
                echo($data);
            }
            //saveDataToFile("numReceived: $numReceived","IctConnectorDebug4.txt");
            if ($numReceived != $contentLength) {
                echo "Received invalid number of content bytes: $numReceived($contentLength),data:$data<BR>";
                return array(-7005,
                         "Received invalid number of content bytes: $numReceived($contentLength)");
            }
        }
        
        return array($statusCode,
                 "");
    }
    
    /**
     *
     * @param object $statusCode
     * @param object $contentLength
     * @param object $fp
     * @return
     */
    private function getHttpResponseContent($statusCode, $contentLength, $fp) {
        if (!$fp) { // this should never occur
            return array(-7000,
                     "Could not access content from Intelliact Connector");
        }
        $data = ""; //default
        if (!feof($fp)) {
            $numChar = 0;
            if ($contentLength > 100000000) {
                echo "Response has too many bytes<BR>";
                return array(-7013,
                         "Response has too many bytes: $contentLength");
            } // something is wrong
            //saveDataToFile("before while","IctConnectorDebug3.txt");
            
            while (!feof($fp)) {
                $numReceived = strlen($data);
                $numMissing = $contentLength - $numReceived;
                if ($numMissing <= 0) {
                    break;
                }
                if ($numMissing + 1 > 1024) {
                    $newdata = fgets($fp, 1024); // for unknown reasons we must not get too many characters at once
                } else {
                    $newdata = fgets($fp, $numMissing + 1); // for unknown reasons we must get one more char
                }
                $numReceivedNew = strlen($newdata);
                $data = $data.$newdata;
                //saveDataToFile("contentLength: $contentLength, numReceived: $numReceived, numReceivedNew: $numReceivedNew","IctConnectorDebug2.txt");
            }
            $numReceived = strlen($data);
            //saveDataToFile("numReceived: $numReceived","IctConnectorDebug3.txt");
            if ($numReceived != $contentLength) {
                echo "Received invalid number of content bytes: $numReceived($contentLength),data:$data<BR>";
                return array(-7005,
                         "Received invalid number of content bytes: $numReceived($contentLength)");
            }
        }
        
        return array($statusCode,
                 $data);
    }
    
    /**
     *
     * @param object $data
     * @param object $fileName
     * @return
     */
    private function saveDataToFile($data, $fileName) {
        global $gTempDirectory;
        $fileName = "$gTempDirectory/$fileName";
        if (!($fp = fopen($fileName, "wb"))) {
            die("could not open output $fileName");
        }
        fwrite($fp, $data, strlen($data));
        fclose($fp);
    }
    
    /**
     *
     * @param object $header
     * @param object $tag
     * @return
     */
    private function getHttpValue($header, $tag) {
        $iStart = strpos(strtolower($header), strtolower($tag));
        if ($iStart === false) {
            return "";
        }
        $value = substr($header, $iStart + strlen($tag));
        //$iEnd = strpos($header,10);
        //echo("getHttpValue: iEnd:$iEnd<BR>");
        //if ($iEnd===false) { $iEnd=strlen($value); }
        $iEnd = $this->findChar($value, 10);
        if ($iEnd == -1) {
            $iEnd = strlen($value);
        }
        
        $value = substr($value, 0, $iEnd);
        
        $value = trim($value);
        return $value;
    }
    
    /**
     *
     * @param object $text
     * @param object $char
     * @return
     */
    private function findChar($text, $char) {
        $iEnd = -1;
        for ($i = 0; $i < strlen($text); $i++) {
            $ch = substr($text, $i, 1);
            $iOrd = ord($ch);
            if (ord($ch) == $char) {
                $iEnd = $i;
                break;
            }
        }
        return $iEnd;
    }
    
    /**
     *
     * @param object $requestID
     * @param object $fileName [optional]
     * @param object $useCache [optional]
     * @return
     */
    private function getFileFromConnector($requestID, $fileName = "", $useCache = false) {
    
        if ($useCache) {
            $data = $this->gFileCache[$fileName];
            if ($data) {
                return $data;
            }
        }
        
        if ($fileName == "") {
            $httpGet = $this->encodeHttpGet(array("getFile", $this->gSessionID, $requestID));
        } else {
            $httpGet = $this->encodeHttpGet(array("getFile", $this->gSessionID, $requestID, $fileName));
        }
        
        $answer = $this->getHttpResponse($httpGet);
        if ($answer[0] < 0) {
            $this->showFatalError("Error", $answer[1], $answer[0]);
        }
        
        if ($useCache) {
            $this->gFileCache[$fileName] = $answer[1];
        }
        return $answer[1];
        
    }
    
}
?>
