<?php
namespace Ouzo;

use InvalidArgumentException;
use Ouzo\Db\StatementExecutor;
use Ouzo\Utilities\Arrays;
use PDO;

class Db
{
    /**
     * @var PDO
     */
    public $_dbHandle = null;
    public $_statementExecutor = null;

    protected $_fetchMode = PDO::FETCH_ASSOC;

    private static $_instance;
    public $_startedTransaction = false;

    public function __construct($loadDefault = true)
    {
        if ($loadDefault) {
            $configDb = Config::getValue('db');
            if (!empty($configDb)) {
                $this->connectDb($configDb);
            }
        }
    }

    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function connectDb($params = array())
    {
        $this->_dbHandle = $this->_createPdo($params);
        return $this;
    }

    public static function callFunction($functionName, $parameters)
    {
        $db = self::getInstance();
        $bindParams = Arrays::toArray($parameters);
        $paramsQueryString = implode(',', array_pad(array(), sizeof($bindParams), '?'));
        return Arrays::first($db->query("SELECT $functionName($paramsQueryString)", $parameters)->fetch());
    }

    public function insert($table, array $data, $sequence = '')
    {
        if (empty($table)) {
            throw new InvalidArgumentException('$table cannot be empty');
        }

        $columns = array_keys($data);
        $values = array_values($data);

        $joinedColumns = implode(', ', $columns);
        $joinedValues = implode(', ', array_fill(0, count($values), '?'));

        $query = "INSERT INTO $table ($joinedColumns) VALUES ($joinedValues)";

        $this->query($query, $values);

        return $sequence ? $this->_dbHandle->lastInsertId($sequence) : null;
    }

    public function update($table, array $data, $where)
    {
        $query = 'UPDATE ' . $table . ' SET ';
        $query .= implode(' = ?, ', array_keys($data)) . ' = ? ';

        if (!empty($where)) {
            $query .= 'WHERE ' . implode(' = ? AND ', array_keys($where)) . ' = ?';
        }

        $values = array_values($data);

        if (!empty($where)) {
            $values = array_merge($values, array_values($where));
        }

        $this->query($query, $values);
    }

    public function query($query, $params = array())
    {
        $this->_statementExecutor = StatementExecutor::prepare($this->_dbHandle, $query, $params);
        return $this->_statementExecutor->execute();
    }

    public function fetchAll()
    {
        return $this->_statementExecutor->fetchAll($this->_fetchMode);
    }

    public function fetch()
    {
        return $this->_statementExecutor->fetch($this->_fetchMode);
    }

    public function rowCount()
    {
        return $this->_statementExecutor->rowCount();
    }

    public function setFetchMode($mode)
    {
        $this->_fetchMode = $mode;
        return $this;
    }

    public function runInTransaction($callable)
    {
        if (!$this->_startedTransaction) {
            $this->_dbHandle->beginTransaction();
            $result = call_user_func($callable);
            $this->_dbHandle->commit();
            return $result;
        }
        return call_user_func($callable);
    }

    public function beginTransaction()
    {
        $this->_startedTransaction = true;
        $this->_dbHandle->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->_dbHandle->commit();
        $this->_startedTransaction = false;
    }

    public function rollbackTransaction()
    {
        $this->_dbHandle->rollBack();
        $this->_startedTransaction = false;
    }

    public function lastErrorMessage()
    {
        $errorInfo = $this->_dbHandle->errorInfo();
        return $errorInfo[2];
    }

    private function _buildDsn($params)
    {
        $charset = Arrays::getValue($params, 'charset');
        $dsn = $params['driver'] . ':host=' . $params['host'] . ';port=' . $params['port'] . ';dbname=' . $params['dbname'] . ';user=' . $params['user'] . ';password=' . $params['pass'];
        return $dsn . ($charset ? ';charset=' . $charset : '');
    }

    private function _createPdo($params)
    {
        $dsn = Arrays::getValue($params, 'dsn');
        if ($dsn) {
            return new PDO($dsn);
        }
        $dsn = $this->_buildDsn($params);
        return new PDO($dsn, $params['user'], $params['pass']);
    }
}