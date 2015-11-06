<?php

	/**
     * ReflectionObject::getProperty/getProperties returns all properties of current class and subclass
     * ReflectionObject::hasProperty checks ONLY if property exists for subclass!
     * new ReflectionObject($this) returns reflection object of subclass
     */
	abstract class ELSTR_DO_Object {

		protected $app;
		protected $db;
		protected $user;
		protected $logger;

		protected $table;

		public $_id;
		public $_insertUser;
		public $_insertDate;
		public $_updateUser;
		public $_updateDate;

		function __construct($app) {
			$this->app = $app;
			$this->db = $app->getBootstrap()->getResource('db')['btext'];
			$this->user = $app->getBootstrap()->getResource('user');
		}

		public function get($_id) {
			$select = $this->db->select();
			$select->from($this->table);
			$select->where('_id = ?', $_id);
			$stmt = $this->db->query($select);
			$row = $stmt->fetch();
			if (is_null($row)) return;

			// Use reflection to fetch the DO's field names
			$class = new ReflectionObject($this);
			$properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

			// Loop through the properties to set them from the current row
			$invalidProps = array();
			foreach ($properties as $property) {
				$name = $property->getName();
				if (array_key_exists($name, $row)) {
					$this->$name = $row[$name];
				} else {
					$invalidProps[] = $name;
				}
			}
			$strInvalidProps = implode(", ", $invalidProps);
			if ($strInvalidProps) {
				$dbname = $this->db->m_dbAdapter->getConfig()['dbname'];
				$message = "Properties '$strInvalidProps' of class '$class->name' are not valid fields in table '$this->table' of database '$dbname'. ";
				$message .= "Please check the name of the properties or make the properties private. ";
				throw new ELSTR_Exception($message);
			}
		}

		public function insert() {
			$row = array();

			// Use reflection to fetch the DO's field names
			$class = new ReflectionObject($this);
			$properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

			// Loop through the properties to set them from the current row
			foreach ($properties as $property) {
				$name = $property->getName();
				// Set only properties of subclass
				if ($property->class == $class->name) {
					$row[$name] = $this->$name;
				}
			}

			try {
				// Insert DO
				$result = $this->db->insert($this->table, $row, $this->user->getUsername());
				$this->_id = $result['_id'];
			} catch (Zend_Db_Statement_Sqlsrv_Exception $e) {
				$invalidProps = array();
				$undefinedProps = array();
				$nullProps = array();
				$columns = $this->db->m_dbAdapter->describeTable($this->table);
				foreach ($properties as $property) {
					$name = $property->getName();
					if (!array_key_exists($name, $columns)) {
						$invalidProps[] = $name;
					}
				}
				foreach ($columns as $name => $column) {
					if (!$column['NULLABLE']) {
						if (!$class->hasProperty($name)) {
							$superClass = new ReflectionObject($class->getParentClass());
							if (!$superClass->hasProperty($name)) {
								$undefinedProps[] = $name;
							}
						} else if (is_null($this->$name)) {
							$property = $class->getProperty($name);
							if ($property->class == $class->name) {
								$nullProps[] = $name;
							}
						}
					}
				}
				$strInvalidProps = implode(", ", $invalidProps);
				$strUndefinedProps = implode(", ", $undefinedProps);
				$strNullProps = implode(", ", $nullProps);
				$dbname = $this->db->m_dbAdapter->getConfig()['dbname'];
				$message = "The provided SQL statement is invalid. ";
				if ($strInvalidProps) {
					$message .= "Properties '$strInvalidProps' of class '$class->name' are not valid fields in table '$this->table' of database '$dbname'. ";
					$message .= "Please check the name of the properties or make the properties private. ";
				}
				if ($strUndefinedProps) {
					$message .= "Fields '$strUndefinedProps' of table '$this->table' of database '$dbname' are undefined in class '$class->name'. ";
					$message .= "Please add properties for all fields which do not allow nulls. ";
				}
				if ($strNullProps) {
					$message .= "Properties '$strNullProps' of class '$class->name' are null. ";
					$message .= "Please provide a value for those properties. ";
				}
				throw new ELSTR_Exception($message);
			}
		}

		public function update() {
			$row = array();

			// Use reflection to fetch the DO's field names
			$class = new ReflectionObject($this);
			$properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

			// Loop through the properties to set them from the current row
			foreach ($properties as $property) {
				$name = $property->getName();
				// Set only DO field names of subclass
				if ($property->class == $class->name) {
					if (!is_null($this->$name)) {
						$row[$name] = $this->$name;
					}
				}
			}

			try {
				// Update DO
				$this->db->update($this->table, $row, "_id = '$this->_id'", $this->user->getUsername());
			} catch (Zend_Db_Statement_Sqlsrv_Exception $e) {
				$invalidProps = array();
				$columns = $this->db->m_dbAdapter->describeTable($this->table);
				foreach ($properties as $property) {
					$name = $property->getName();
					if (!array_key_exists($name, $columns)) {
						$invalidProps[] = $name;
					}
				}
				$strInvalidProps = implode(", ", $invalidProps);
				$message = "The provided SQL statement is invalid. ";
				if ($strInvalidProps) {
					$message .= "Properties '$strInvalidProps' of class '$class->name' are not valid fields in table '$this->table' of database '$dbname'. ";
					$message .= "Please check the name of the properties or make the properties private. ";
				}
				throw new ELSTR_Exception($message);
			}
		}

		public function delete() {
			// Delete DO
			$this->db->delete($this->table, "_id = '$this->_id'", $this->user->getUsername());

			// Use reflection to fetch the DO's field names
			$class = new ReflectionObject($this);
			$properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

			// Loop through the properties to set them from the current row
			foreach ($properties as $property) {
				$name = $property->getName();
				// Set only DO field names of subclass
				if ($property->class == $class->name) {
					if (!is_null($this->$name)) {
						$this->$name = null;
					}
				}
			}
		}

		public function find() {
			// Use reflection to fetch the DO's field names
			$class = new ReflectionObject($this);
			$properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

			// Loop through the properties to set them from the current row
			$select = $this->db->select();
			foreach ($properties as $property) {
				$name = $property->getName();
				// Set only DO field names of subclass
				if ($property->class == $class->name) {
					if (!is_null($this->$name)) {
						$select->where("$name = ?", $this->$name);
					}
				}
			}

			$select->from($this->table);
			$stmt = $this->db->query($select);
			$rs = $stmt->fetchAll();
			require_once ('ELSTR_ReadOnlyResultSet.php');
			return new ELSTR_ReadOnlyResultSet($rs);
		}

		public function children($child, $reference) {
			$childClass = new ReflectionObject($child);
			if (is_null($this->_id)) {
				$class = new ReflectionObject($this);
				$message = "Object of class '$class->name' has not been initialized. Please call get() or set the id.";
				throw new ELSTR_Exception($message);
			}
			$children = array();
			$child->$reference = $this->_id;
			$rs = $child->find();
			for ($i = 0; $i < $rs->rowCount(); $i++) {
				require_once ("$childClass->name.php");
				$nextChild = new $childClass->name($this->app);
				$children[] = $rs->getNext($nextChild);
			}
			return $children;
		}

		public function parent($parent, $reference) {
			if (is_null($this->$reference)) {
				$class = new ReflectionObject($this);
				$message = "Object of class '$class->name' has not been initialized correctly. Please call get() or set the reference id.";
				throw new ELSTR_Exception($message);
			}
			$parent->get($this->$reference);
			return $parent;
		}

	}

?>