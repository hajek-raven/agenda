<?php
namespace App\Model\Common;

class TableModel extends BaseModel
{
	protected $tableName;
	protected $primaryKey = "id";

	public function __construct(\DibiConnection $connection, $tableName)
 	{
		$this->tableName = $tableName;
		$baseSelect = $connection->select('*')->from($this->tableName);
		parent::__construct($connection,$baseSelect);
 	}

	public function setPrimaryKey($columnName)
	{
		$this->primaryKey = $columnName;
	}

	public function getTableName()
	{
		return $this->tableName;
	}

	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	public function delete($id)
	{
		$r = $this->getConnection()->delete($this->getTableName())->where($this->getPrimaryKey() . " = " . $id)->execute();
		return $r;
	}

	public function update($id, $data)
	{
		$r = $this->getConnection()->update($this->getTableName(),$data)->where($this->getPrimaryKey() . " = " . $id)->execute();
		return $r;
	}

	public function insert($data)
	{
		$r = $this->getConnection()->insert($this->getTableName(),$data)->execute();
		return $this->getInsertId();
	}

	public function dumbInsert($data)
	{
		$r = $this->getConnection()->insert($this->getTableName(),$data)->execute();
	}

	public function find($id)
	{
		return $this->findBy(array($this->getPrimaryKey() => $id));
	}

	public function get($id)
	{
		return $this->getBy(array($this->getPrimaryKey() => $id));
	}
}
