<?php

	class ELSTR_ReadOnlyResultSet {
		// This member variable will hold the native result set
		private $rs;

		// Assign the native result set to an instance variable
		function __construct($rs) {
			$this->rs = $rs;
		}

		// Receives an instance of the DataObject we're working on
		function getNext($dataobject) {
			$row = current($this->rs);
			next($this->rs);

			// Use reflection to fetch the DO's field names
			$class = new ReflectionObject($dataobject);
			$properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

			// Loop through the properties to set them from the current row
			foreach ($properties as $property) {
				$name = $property->getName();
				$dataobject->$name = $row[$name];
			}

			return $dataobject;
		}

		// Move the pointer back to the beginning of the result set
		function reset() {
			reset($this->rs);
		}

		// Return the number of rows in the result set
		function rowCount() {
			return count($this->rs);
		}
	}

?>