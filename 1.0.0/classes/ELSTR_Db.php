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

    function __construct($dbAdapter)
    {
        $this->m_dbAdapter = $dbAdapter;
    }

    /**
    * Override:
    * Handle request
    *
    * @param mixed $table The table to insert data into.
    * @param array $bind Column-value pairs.
    * @return array
    */
    public function insert($table, $bind, $userId = '')
    {
        $insertDefaultValues = $this->getInsertDefaultData($table, $userId);
        $bind = array_merge($bind, $insertDefaultValues);
        $affectedRows = $this->m_dbAdapter->insert($table, $bind);

        return array('count' => $affectedRows, '_id' => $insertDefaultValues['_id']);
    }

    /**
    * Prepares and executes an SQL statement with bound data.
    *
    * @param mixed $sql The SQL statement with placeholders.
    *                        May be a string or Zend_Db_Select.
    * @param mixed $bind An array of data to bind to the placeholders.
    * @return Zend_Db_Statement_Interface
    */
    public function query($sql, $bind = array())
    {
        return $this->m_dbAdapter->query($sql, $bind);
    }

    private function getInsertDefaultData($table, $userId)
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
}

?>