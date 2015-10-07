<?php
namespace App\Model\Common;

abstract class GridTableModel extends TableModel implements \Grido\DataSources\IDataSource
{
	public function __construct($connection,$tableName)
 	{
 		parent::__construct($connection,$tableName);
  }

  public function getData()
  {
  	return $this->getSelection()->fetchAll();
  }

	public function getRow($id, $idCol)
	{
		$fluent = clone $this->getSelection();
		return $fluent->where("%n = %s", $idCol, $id)->fetch();
	}

  public function filter(array $conditions)
  {
  	foreach ($conditions as $condition)
		{
    	$this->makeWhere($condition);
    }
  }

  public function limit($offset, $limit)
  {
  	$this->getSelection()->limit($limit)->offset($offset);
  }

  public function sort(array $sorting)
  {
		foreach ($sorting as $column => $sort)
		{
			$this->getSelection()->orderBy("%n", $column, $sort);
		}
  }

	public function suggest($column, array $conditions, $limit)
	{
		$fluent = clone $this->getSelection();
		is_string($column) && $fluent->removeClause('SELECT')->select("DISTINCT $column");

		foreach ($conditions as $condition)
		{
			$this->makeWhere($condition, $fluent);
		}

		$items = array();
		$data = $fluent->fetchAll(0, $limit);
		foreach ($data as $row)
		{
			if (is_string($column))
			{
				$value = (string) $row[$column];
			}
			elseif (is_callable($column))
			{
				$value = (string) $column($row);
			}
			else
			{
				$type = gettype($column);
				throw new \InvalidArgumentException("Column of suggestion must be string or callback, $type given.");
			}
			$items[$value] = \Nette\Templating\Helpers::escapeHtml($value);
		}

		return array_values($items);
	}

	protected function makeWhere(\Grido\Components\Filters\Condition $condition, \DibiFluent $fluent = NULL)
	{
		$fluent = $fluent === NULL ? $this->getSelection() : $fluent;
		if ($condition->callback)
		{
			callback($condition->callback)->invokeArgs(array($condition->value, $fluent));
		}
		else
		{
			call_user_func_array(array($fluent, 'where'), $condition->__toArray('[', ']'));
		}
	}
}
