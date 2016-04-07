<?php

/**
* Class to create ELSTR specific DB
*
* @author Marco Egli, Felix Nyffenegger
* @version 1.0
* @created 19-Okt-2009 17:14:59
*/
class ELSTR_Db {
    var $m_dbAdapter;
    var $m_logger;
    var $m_profilerEnabled;

    function __construct($dbAdapter,$logger,$profilerEnabled)
    {
        $this->m_dbAdapter = $dbAdapter;
        $this->m_logger = $logger;
        $this->m_profilerEnabled = $profilerEnabled;
    }

    /**
    * Override: Insert
    *
    * @param mixed $table The table to insert data into.
    * @param array $bind Column-value pairs.
    * @param string $userId
    * @return array
    */
    public function insert($table, $bind, $userId = '')
    {
        //$insertDefaultValues = $this->_getInsertDefaultData($table, $userId);
        //$bind = array_merge($bind, $insertDefaultValues);
        //$affectedRows = $this->m_dbAdapter->insert($table, $bind);

        $exception = null;
        try {
            $insertDefaultValues = $this->_getInsertDefaultData($table, $userId);
            $bind = array_merge($bind, $insertDefaultValues);
            $affectedRows = $this->m_dbAdapter->insert($table, $bind);
        } catch (Exception $e) {
            $this->kurs->logger->err($e);
            $exception = $e;
        }

        if (isset($this->m_logger) && $this->m_profilerEnabled === true) {
            $this->m_logger->debug($this->m_dbAdapter->getProfiler()->getLastQueryProfile()->getQuery());
        }
        if ($exception !== null) throw $e;
        return array('count' => $affectedRows, '_id' => $insertDefaultValues['_id']);

    }

	/**
	 * Override: update
	 *
	 * @param mixed $table The table to insert data into.
	 * @param array $bind Column-value pairs.
	 * @param mixes $where update where clause
	 * @param string $userId
	 * @return integer $affectedRows Number of affected rows
	 */
	public function update($table, $bind, $where, $userId = '')	{
		//$updateDefaultValues = $this->_getUpdateDefaultData($userId);
		//$bind = array_merge($bind, $updateDefaultValues);
		//$affectedRows = $this->m_dbAdapter->update($table, $bind, $where);

        $exception = null;
        try {
            $updateDefaultValues = $this->_getUpdateDefaultData($userId);
            $bind = array_merge($bind, $updateDefaultValues);
            $affectedRows = $this->m_dbAdapter->update($table, $bind, $where);
        } catch (Exception $e) {
            $this->m_logger->err($e);
            $exception = $e;
        }

        if (isset($this->m_logger) && $this->m_profilerEnabled === true) {
            $this->m_logger->debug($this->m_dbAdapter->getProfiler()->getLastQueryProfile()->getQuery());
        }
        if ($exception !== null) throw $e;
        return $affectedRows;

	}


	/**
	 * Override: delete
	 *
	 * @param mixed $table The table to insert data into.
	 * @param mixes $where update where clause
	 * @return integer $affectedRows Number of affected rows
	 */
	public function delete($table, $where) {
		// $affectedRows = $this->m_dbAdapter->delete($table, $where);

        $exception = null;
        try {
            $affectedRows = $this->m_dbAdapter->delete($table, $where);
        } catch (Exception $e) {
            $this->m_logger->err($e);
            $exception = $e;
        }

        if (isset($this->m_logger) && $this->m_profilerEnabled === true) {
            $this->m_logger->debug($this->m_dbAdapter->getProfiler()->getLastQueryProfile()->getQuery());
        }
        if ($exception !== null) throw $e;
        return $affectedRows;

	}


    /**
    * Prepares and executes an SQL statement with bound data.
    *
    * @param mixed $sql The SQL statement with placeholders.
    *                        May be a string or Zend_Db_Select.
    * @param mixed $bind An array of data to bind to the placeholders.
    * @return Zend_Db_Statement_Interface
    */
    public function query($sql, $bind = array()) {
        //return $this->m_dbAdapter->query($sql, $bind);

        $exception = null;
        try {
            $result = $this->m_dbAdapter->query($sql, $bind);
        } catch (Exception $e) {
            $this->m_logger->err($e);
            $exception = $e;
        }
        
        if (isset($this->m_logger) && $this->m_profilerEnabled === true) {
            $this->m_logger->debug($this->m_dbAdapter->getProfiler()->getLastQueryProfile()->getQuery());
        }
        if ($exception !== null) throw $e;
        return $result;

    }

	public function select(){
		return $this->m_dbAdapter->select();
	}

    public function beginTransaction(){
        return $this->m_dbAdapter->beginTransaction();
    }

    public function commit(){
        return $this->m_dbAdapter->commit();
    }

    public function rollBack(){
        return $this->m_dbAdapter->rollBack();
    }

    private function _getInsertDefaultData($table, $userId)
    {
        $creaUser = $userId;
        $updaUser = $userId;
        $result[0] = 1;
        while (count($result) > 0) {
            $Id = md5(uniqid(rand(), true));
            $select = $this->m_dbAdapter->select()->from ($table)->where("_id = '$Id'");
            $stmt = $this->m_dbAdapter->query($select);
            $result = $stmt->fetchAll();
        }
        $creaDate = Zend_Date::now()->toString('YYYY-MM-dd HH:mm:ss');
        $updaDate = $creaDate;
        return array ('_id' => $Id,
            '_insertDate' => $creaDate,
            '_insertUser' => $creaUser,
            '_updateDate' => $updaDate,
            '_updateUser' => $updaUser);
    }


	private function _getUpdateDefaultData($userId)
	{
		$updaUser = $userId;
		$updaDate = Zend_Date::now()->toString('YYYY-MM-dd HH:mm:ss');
		return array ('_updateDate' => $updaDate,
		    '_updateUser' => $updaUser);
	}

}

