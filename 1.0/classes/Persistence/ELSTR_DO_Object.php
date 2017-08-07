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

		protected $map;
		protected $table;

		public $_id;
		public $_insertUser;
		public $_insertDate;
		public $_updateUser;
		public $_updateDate;

		function __construct($app, $server) {
			$this->app = $app;
			$this->db = $app->getBootstrap()->getResource('db')[$server];
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
				if (array_key_exists($this->map($name), $row)) {
					$this->$name = $row[$this->map($name)];
				} else {
					$invalidProps[] = $name;
				}
			}
			$strInvalidProps = implode(", ", $invalidProps);
			if ($strInvalidProps) {
				$dbname = $this->db->m_dbAdapter->getConfig()['dbname'];
				$message = "Properties '$strInvalidProps' of class '$class->name' are not valid fields in table '$this->table' of database '$dbname'. ";
				$message .= "Please check the name and mapping of the properties or make the properties private. ";
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
					$row[$this->map($name)] = $this->$name;
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
					if (!array_key_exists($this->map($name), $columns)) {
						$invalidProps[] = $name;
					}
				}
				foreach ($columns as $name => $column) {
					if (!$column['NULLABLE']) {
						if (!$class->hasProperty($this->map($name))) {
							$superClass = new ReflectionObject($class->getParentClass());
							if (!$superClass->hasProperty($this->map($name))) {
								$undefinedProps[] = $name;
							}
						} else if (is_null($this[$this->map($name)])) {
							$property = $class->getProperty($this->map($name));
							if ($property->class == $class->name) {
								$nullProps[] = $this->map($name);
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
					$message .= "Please check the name and mapping of the properties or make the properties private. ";
				}
				if ($strUndefinedProps) {
					$message .= "Fields '$strUndefinedProps' of table '$this->table' of database '$dbname' are undefined in class '$class->name'. ";
					$message .= "Please add properties or check the mapping for all fields which do not allow nulls. ";
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
						$row[$this->map($name)] = $this->$name;
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
					if (!array_key_exists($this->map($name), $columns)) {
						$invalidProps[] = $name;
					}
				}
				$strInvalidProps = implode(", ", $invalidProps);
				$message = "The provided SQL statement is invalid. ";
				if ($strInvalidProps) {
					$message .= "Properties '$strInvalidProps' of class '$class->name' are not valid fields in table '$this->table' of database '$dbname'. ";
					$message .= "Please check the name and mapping of the properties or make the properties private. ";
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
						$columnName = $this->map($name);
						$select->where("$columnName = ?", $this->$name);
					}
				}
			}

			$select->from($this->table);
			$stmt = $this->db->query($select);
			$rs = $stmt->fetchAll();

			$dataobjects = array();
			foreach ($rs as $row) {
				$dataobject = new $class->name($this->app);
				reset($properties);
				foreach ($properties as $property) {
					$name = $property->getName();
					$dataobject->$name = $row[$this->map($name)];
				}
				$dataobjects[] = $dataobject;
			}
			return $dataobjects;
		}

		public function children($child, $reference) {
			$childClass = new ReflectionObject($child);
			if (is_null($this->_id)) {
				$class = new ReflectionObject($this);
				$message = "Object of class '$class->name' has not been initialized. Please call get() or set the id.";
				throw new ELSTR_Exception($message);
			}
			$child->$reference = $this->_id;
			$children = $child->find();
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

		protected function setMap($map) {
			if (!is_null($map) && count($map) > 0) {
				$flipMap = array_flip($map);
				if (array_intersect_key($map, $flipMap)) {
					$message = "Values of mapping are not allowed to have the same value as one of the keys. ";
					$message .= "Please provide another key or value, or remove keys which are equal to their own values. ";
					throw new ELSTR_Exception($message);
				}
				$this->map = array_merge($map, $flipMap);
			}
		}

		protected function map($property) {
			if (is_null($this->map)) {
				return $property;
			} else if (!array_key_exists($property, $this->map)) {
				return $property;
			} else {
				return $this->map[$property];
			}

		}

	}

?>
