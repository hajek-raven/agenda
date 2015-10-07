<?php
namespace App\Model\Work;

class Sets extends \App\Model\Common\GridTableModel
{
	public function __construct(\DibiConnection $connection)
 	{
		parent::__construct($connection, "wrk_set");
  }

	public function asArray()
	{
		$result = array();
		$sql = "SELECT id, name FROM " . $this->getTableName() . " ORDER BY `name`";
		$data = $this->query($sql)->fetchAll();
		foreach($data as $record)
		{
			$result[$record->id] = $record->name;
		}
		return $result;
	}

	public function ActiveAsArray(array $add = array())
	{
		$result = array();
		$sql = "SELECT id, name FROM " . $this->getTableName() . " WHERE active = 1";
		if ($add)
		{
			$list = implode(", ",$add);
			$sql .= " OR id IN(".$list.")";
		}
		$sql .= " ORDER BY `name`";
		$data = $this->query($sql)->fetchAll();
		foreach($data as $record)
		{
			$result[$record->id] = $record->name;
		}
		return $result;
	}
}
