<?php

$phpExcelOptions = $application->getOption("phpExcel");

/** Include path **/
set_include_path(APPLICATION_PATH . '/phplib/phpExcel/' . $phpExcelOptions["version"] . '/Classes/'.PATH_SEPARATOR.get_include_path());

/** PHPExcel */
include 'PHPExcel.php';



/**
* Class to create ELSTR specific Reports
*
* @author Marco Egli, Felix Nyffenegger
* @version 1.0
* @created 03-Mar-2010 17:14:59
*/
class ELSTR_ReportEngine {
    var $m_objPHPExcel;
    var $m_columns;
    var $m_data;
    var $m_file;

    function __construct()
    {
        $this->m_objPHPExcel = new PHPExcel();
        $this->m_file = tempnam(sys_get_temp_dir(),"rep");
    }
    
    /**
    * Set the columns and headers
    *
    * @param $array $columns
    * @return void
    */
    public function setColumns($columns){
    	$this->m_columns = $columns;
    	
    	return true;
    }
    
    /**
    * Set the data in the report
    *
    * @param $array $data
    * @return void
    */    
    public function setData($data){
    	$this->m_data = $data;
    	
    	return true;
    }

    /**
    * Creates the report object
    *
    * @param $array $data
    * @return void
    */ 
    public function createReport(){
    
    
    	//print_r($this->m_columns);
    	
    	//print_r($this->m_data);
    	
    	// Write the first row
    	// Write the header row
    	for ($i = 0; $i < count($this->m_columns); $i++) {
			
    		$col = chr($i+ord('A'));
    		
    		$key = $this->m_columns[$i]['key'];
    		$label = $this->m_columns[$i]['label'];
    		$width = (integer) $this->m_columns[$i]['width'];
			
    		$keyColumn[$key] = $col;
			
    		$cell = $col."1";
    		$this->m_objPHPExcel->getActiveSheet()->SetCellValue($cell, $label);
    		$this->m_objPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth($width/5);
		}
    	
  
        // Write the data rows
    	for ($i = 0; $i < count($this->m_data); $i++) {
			
    		$rowData = $this->m_data[$i];
    		
    		$keyArray = array_keys($rowData);
    		for ($n = 0; $n < count($keyArray); $n++) {
    			$key = $keyArray[$n];
    			$value = $rowData[$key];

    			if(isset($keyColumn[$key])){
    			    $row = (string) $i+2;
		    		$cell = $keyColumn[$key].$row;
		    		$this->m_objPHPExcel->getActiveSheet()->SetCellValue($cell, $value);
    			}
    		}

		}    	
   
		$this->m_objPHPExcel->getActiveSheet()->setTitle('Report');
		
    	return true;
    }
    
    
    /**
    * Get the file stream of the report
    *
    * @param string $type (xlsx,pdf)
    * @return stream
    */
    public function getFile($type){   
    
	    switch ($type) {
	    case "pdf":
		    /** PHPExcel_Writer_PDF */
			include 'PHPExcel/Writer/PDF.php';	        
	        $objWriter = new PHPExcel_Writer_PDF($this->m_objPHPExcel);
	        break;
	    case "xls":
		    /** PHPExcel_Writer_Excel5 */
			include 'PHPExcel/Writer/Excel5.php';	        
	        $objWriter = new PHPExcel_Writer_Excel5($this->m_objPHPExcel);
	        break;	        
	    default: 
		    /** PHPExcel_Writer_Excel2007 */
			include 'PHPExcel/Writer/Excel2007.php';
			$objWriter = new PHPExcel_Writer_Excel2007($this->m_objPHPExcel);    	
		}

		$objWriter->save($this->m_file);
		return $this->m_file;
    
    }
    
    

}

?>