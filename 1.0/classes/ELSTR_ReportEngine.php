<?php

$phpExcelOptions = $application->getOption("phpExcel");

/** Include path **/
set_include_path(APPLICATION_PATH . '/phplib/phpExcel/' . $phpExcelOptions["version"] . '/Classes/'.PATH_SEPARATOR.get_include_path());

/** PHPExcel */
include 'PHPExcel.php';



/**
 * Class to create ELSTR specific Reports
 *
 * @author Marco Egli, Felix Nyffenegger, Martin Bichsel
 * @version 1.0
 * @created 03-Mar-2010 17:14:59
 */
class ELSTR_ReportEngine {
	var $m_objPHPExcel;
	var $m_reportElements;
	var $m_title;
	var $m_autoColWidth;
	var $m_file;
	

	/**
	 * Constructor
	 *
	 * @param string $fileName name of an Excel template
	 * @param string $excelVersion version of excel template (only used to overwrite default)
	 * @return void
	 */
	function __construct($fileName = '',$excelVersion='')
	{
		$fullFileName = APPLICATION_PATH.'/application/reportTemplates/'.$fileName;
		$this->m_autoColWidth = false;
		
		if ($excelVersion =='') {
			// FIXME
			if (strpos($fullFileName,'.xlsx')>0) {
				$excelVersion='Excel2007';
			}
			else if (strpos($fullFileName,'.xls')>0) {
				$excelVersion='Excel5';
			}
		}

		if ($excelVersion=='Excel5')
		{
			include "PHPExcel/Reader/Excel5.php";
			$objReader = new PHPExcel_Reader_Excel5();
			$this->m_objPHPExcel  = $objReader->load($fullFileName);
		}
		else if ($excelVersion=='Excel2003XML')
		{
			include "PHPExcel/Reader/Excel2003XML.php";
			$objReader = new PHPExcel_Reader_Excel2003XML();
			$this->m_objPHPExcel  = $objReader->load($fullFileName);
		}
		else if ($excelVersion=='Excel2007')
		{
			include "PHPExcel/Reader/Excel2007.php";
			$objReader = new PHPExcel_Reader_Excel2007();
			$this->m_objPHPExcel  = $objReader->load($fullFileName);
		}
		else
		{
			$this->m_objPHPExcel = new PHPExcel();
			$this->m_title = 'Report';
			$this->m_autoColWidth = true;
		}

		$this->m_reportElements = array();
		$this->m_file = tempnam(sys_get_temp_dir(),"rep");
	}


	/**
	 * Add a table, consisting of columns(header) and data
	 *
	 * @param array $columns
	 * @param array $data
	 * @param string $cellSpecification, 'byColumnKey' or a specification of the topleft cell in the format according to locateReportElement
	 * @return void
	 */
	public function addTable($columns,$data,$cellSpecification='byColumnKey'){
		$table=array();

		//determine location of the head element of each column
		$isLocatable = false;
		if ($cellSpecification == 'byColumnKey')
		{
			for ($i = 0; $i < count($columns); $i++) {
				$key = $columns[$i]['key'];
				$columns[$i] = $this->locateReportElement($columns[$i],'{'.$key.'}');
				if (isset($columns[$i]['col']))
				{
					$table['row'] = $columns[$i]['row']; // keep row of last header element
					$isLocatable = true;
				}
			}
		}
		else
		{
			$table = $this->locateReportElement($table,$cellSpecification);
			if (isset($table['col']))
			{
				$isLocatable = true;
				for ($i = 0; $i < count($columns); $i++) {
					$columns[$i]['col']=$table['col']+$i;
					$columns[$i]['row']=$table['row'];
				}
			}
		}

		if ($isLocatable)
		{
			$table['columns'] = $columns;
			$table['data'] = $data;
			$table['type'] = 'table';
			$this->m_reportElements[] = $table;
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Add a value to a cell
	 *
	 * @param string $value
	 * @param string $cellSpecification
	 * @return void
	 */
	public function addValue($value,$cellSpecification='A1'){
		//echo("addValue: value: $value, cellSpecification: $cellSpecification");
		$cell=array();
		$cell = $this->locateReportElement($cell,$cellSpecification);

		if (isset($cell['col']))
		{
			$cell['value'] = $value;
			$cell['type'] = 'cell';
			$this->m_reportElements[] = $cell;
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Add values to cells, located by $key
	 *
	 * @param array $values
	 * @return void
	 */
	public function addValues($values){
		foreach ($values as $key => $value)
		{
		   $this->addValue($value, '{'.$key.'Cell}');
		}
	}


	/**
	 * Set locates the report element using $cellSpecification
	 *
	 * @param array $reportElement a report elememt (e.g cell, table)
	 * @param string $cellSpecification either simple cell coordinate (e.g A1) or cell value starting with a non-alphabetic character (e.g. $name), to be located
	 * @return array $reportElement including row and column if the element could be located
	 */
	private function locateReportElement($reportElement,$cellSpecification) {
		//echo "locateReportElement $cellSpecification\n";
		$prefix = substr($cellSpecification,0,5);
		if ($prefix=='cell:') // assuming simple address, e.g. A1
		{
			$specification = substr($cellSpecification,5);
			$excelCell = $this->m_objPHPExcel->getActiveSheet()->getCell($specification);
			if (!$excelCell) { return; }
			$reportElement['col'] = PHPExcel_Cell::columnIndexFromString( $excelCell->getColumn() )-1;
			$reportElement['row'] = $excelCell->getRow();
		}
		else
		{
			$rowIterator = $this->m_objPHPExcel->getActiveSheet()->getRowIterator();
			foreach ($rowIterator as $row) {
				$cellIterator = $row->getCellIterator();
  				$cellIterator->setIterateOnlyExistingCells(true);
  				foreach ($cellIterator as $excelCell) {
  					$value = (string) $excelCell->getValue();
  					//if ($value){ echo "locateReportElement value $value\n";}
  					if (strpos($value,$cellSpecification)===0) // for unknown reasons $value sometimes appears duplicated
  					{
   						$reportElement['col'] = PHPExcel_Cell::columnIndexFromString( $excelCell->getColumn() )-1;
  						$reportElement['row'] = $excelCell->getRow();
  						return $reportElement;
  					}
  				}
			}
		}
		return $reportElement;
	}


	/**
	 * Creates the report object
	 *
	 * @param array $data
	 * @return void
	 */
	public function createReport(){

		foreach ($this->m_reportElements as $reportElement) {
			$type = $reportElement['type'];
			if ($type == 'table')
			{
				$this->setTableData($reportElement);
				$this->setTableBodyStyle($reportElement);
			}
			else if ($type == 'cell')
			{
				$this->setCellData($reportElement);
			}
		}

		if (isset($this->m_title))
		{
			$this->m_objPHPExcel->getActiveSheet()->setTitle($this->m_title);
		}

		return true;
	}


	/**
	 * Integrates a cell into a report
	 *
	 * @param array $reportElement
	 * @return void
	 */
	private function setCellData($reportElement)
	{
		$value = $reportElement['value'];
		$col =     $reportElement['col'];
		$row =     $reportElement['row'];
		$cell = $this->m_objPHPExcel->getActiveSheet()->GetCellByColumnAndRow($col,$row);
		$cell->setValue($value);
	}


	/**
	 * Integrates a table into a report
	 *
	 * @param array $reportElement
	 * @return void
	 */
	private function setTableData($reportElement)
	{
		$columns = $reportElement['columns'];
		$data    = $reportElement['data'];
		$numRow  = count($data);
		$sheet = $this->m_objPHPExcel->getActiveSheet();
		
		// Write the header row
		for ($i = 0; $i < count($columns); $i++) {

			$column = $columns[$i];
			if (isset($column['col']))
			{
				$col = $column['col'];
				$row = $column['row'];
				$key = $column['key'];
				$keyColumn[$key] = $i;
				
				$valueBelowTable = $sheet->GetCellByColumnAndRow($col,$row+2)->getValue();
				$sheet->SetCellValueByColumnAndRow($col,$row+$numRow+1, $valueBelowTable);

				$label = $columns[$i]['label'];
				$sheet->SetCellValueByColumnAndRow($col,$row, $label);

				if (isset($column['width']) && $this->m_autoColWidth){
					$width = (integer) $column['width'];
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth($width/5);
				}
			}
		}

		// Write the data rows
		for ($j = 0; $j < count($data); $j++) {
			$rowData = $data[$j];

			$keyArray = array_keys($rowData);
			for ($i = 0; $i < count($keyArray); $i++) {
				$key = $keyArray[$i];
				if (isset($keyColumn[$key]))
				{
					$value = $rowData[$key];
					$iCol = $keyColumn[$key];
					$column = $columns[$iCol];

					if(isset($column['col'])){

						$col = $column['col'];
						$row = $column['row']+ 1 + $j;

						$cell = $sheet->GetCellByColumnAndRow($col,$row);
						$cell->setValue($value);
					}
				}
			}
		}
	}

	
	/**
	 * Set style of table body
	 *
	 * @param array $reportElement
	 * @return void
	 */
	private function setTableBodyStyle($reportElement)
	{
		$sheet = $this->m_objPHPExcel->getActiveSheet();
		$columns = $reportElement['columns'];
		$data    = $reportElement['data'];
		$numRow  = count($data);
		$headerRow = $reportElement['row'];
		$rowHeight = $sheet->getRowDimension($headerRow)->getRowHeight();
		$nextRowHeight = $sheet->getRowDimension($headerRow+1)->getRowHeight();
		for ($i = 0; $i < count($columns); $i++) {

			$column = $columns[$i];
			if (isset($column['col']))
			{
				$col = $column['col'];
				$row = $column['row'];
	
				$coordinate1 = PHPExcel_Cell::stringFromColumnIndex($col).($row+1);
				$coordinate2 = PHPExcel_Cell::stringFromColumnIndex($col).($row+$numRow);
				$coordinate3 = PHPExcel_Cell::stringFromColumnIndex($col).($row+$numRow+1);
				$outsideStyle = $sheet->getStyleByColumnAndRow($col,$row+2);
				$sheet->duplicateStyle ($outsideStyle,"$coordinate3:$coordinate3");
				if ($numRow>0)
				{
				    $style = $sheet->getStyleByColumnAndRow($col,$row+1);
					$sheet->duplicateStyle ($style,"$coordinate1:$coordinate2");
				}
			}
		}
		for ($j = $headerRow+1; $j < $headerRow+1+$numRow; $j++) {
			$sheet->getRowDimension($j)->setRowHeight($rowHeight); // for unknown reasons row heights get screwed up during cell manipulation, reseting them here
		}
		$sheet->getRowDimension($headerRow+1+$numRow)->setRowHeight($rowHeight); // for unknown reasons row heights get screwed up during cell manipulation, reseting them here
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