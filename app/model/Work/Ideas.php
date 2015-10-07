<?php
namespace App\Model\Work;

class Ideas extends \App\Model\Common\GridTableModel
{
	public function __construct(\DibiConnection $connection)
 	{
		parent::__construct($connection, "wrk_assignment");
		$this->getSelection()->removeClause("SELECT");
		$this->getSelection()->select("wrk_assignment.*, user.firstname, user.lastname, count(wrk_work.id) as works")
			->leftJoin("user")->on("wrk_assignment.user_id = user.id")
			->leftJoin("wrk_work")->on("wrk_assignment.id = wrk_work.wrk_assignment_id")
			->groupBy("wrk_assignment.id");
		$this->setPrimaryKey("wrk_assignment.id");
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

	public function getGoalsAsArray($assignmentId)
	{
		$result = array();
		$data = $this->query("SELECT * FROM wrk_assignment_goal WHERE wrk_assignment_id = $assignmentId ORDER BY `order`")->fetchAll();
		foreach($data as $item)
		{
			$result[$item->order] = $item->description;
		}
		return $result;
	}

	public function getOutlineAsArray($assignmentId)
	{
		$result = array();
		$data = $this->query("SELECT * FROM wrk_assignment_outline WHERE wrk_assignment_id = $assignmentId ORDER BY `order`")->fetchAll();
		foreach($data as $item)
		{
			$result[$item->order] = $item->description;
		}
		return $result;
	}

	public function getGoals($assignmentId)
	{
		$result = array();
		$data = $this->query("SELECT * FROM wrk_assignment_goal WHERE wrk_assignment_id = $assignmentId ORDER BY `order`")->fetchAll();
		foreach($data as $item)
		{
			$result[] = $item;
		}
		return $result;
	}

	public function getOutline($assignmentId)
	{
		$result = array();
		$data = $this->query("SELECT * FROM wrk_assignment_outline WHERE wrk_assignment_id = $assignmentId ORDER BY `order`")->fetchAll();
		foreach($data as $item)
		{
			$result[] = $item;
		}
		return $result;
	}

	public function addGoal($id,$description)
	{
		$max = $this->query("SELECT max(`order`) as max FROM wrk_assignment_goal WHERE wrk_assignment_id = $id")->fetch()->max;
		$max++;
		$this->query("INSERT INTO wrk_assignment_goal (`wrk_assignment_id`,`order`,`description`) VALUES ($id,$max,'$description')");
	}

	public function addOutline($id,$description)
	{
		$max = $this->query("SELECT max(`order`) as max FROM wrk_assignment_outline WHERE wrk_assignment_id = $id")->fetch()->max;
		$max++;
		$this->query("INSERT INTO wrk_assignment_outline (`wrk_assignment_id`,`order`,`description`) VALUES ($id,$max,'$description')");
	}

	public function deleteGoal($id,$order)
	{
		$this->query("DELETE FROM `wrk_assignment_goal` WHERE `wrk_assignment_id` = $id AND `order` = $order");
		$this->query("UPDATE `wrk_assignment_goal` SET `order` = `order` - 1 WHERE `wrk_assignment_id` = $id AND `order` > $order");
	}

	public function deleteOutline($id,$order)
	{
		$this->query("DELETE FROM `wrk_assignment_outline` WHERE `wrk_assignment_id` = $id AND `order` = $order");
		$this->query("UPDATE `wrk_assignment_outline` SET `order` = `order` - 1 WHERE `wrk_assignment_id` = $id AND `order` > $order");
	}

	public function upGoal($id,$order)
	{
		$previous = $this->query("SELECT max(`order`) as position FROM wrk_assignment_goal WHERE wrk_assignment_id = $id AND `order` < $order")->fetch()->position;
		if ($previous)
		{
			$this->query("UPDATE `wrk_assignment_goal` SET `order` = 9999 WHERE `wrk_assignment_id` = $id AND `order` = $order");
			$this->query("UPDATE `wrk_assignment_goal` SET `order` = $order WHERE `wrk_assignment_id` = $id AND `order` = $previous");
			$this->query("UPDATE `wrk_assignment_goal` SET `order` = $previous WHERE `wrk_assignment_id` = $id AND `order` = 9999");
		}
		return $previous;
	}

	public function downGoal($id,$order)
	{
		$next = $this->query("SELECT min(`order`) as position FROM wrk_assignment_goal WHERE wrk_assignment_id = $id AND `order` > $order")->fetch()->position;
		if ($next)
		{
			$this->query("UPDATE `wrk_assignment_goal` SET `order` = 9999 WHERE `wrk_assignment_id` = $id AND `order` = $order");
			$this->query("UPDATE `wrk_assignment_goal` SET `order` = $order WHERE `wrk_assignment_id` = $id AND `order` = $next");
			$this->query("UPDATE `wrk_assignment_goal` SET `order` = $next WHERE `wrk_assignment_id` = $id AND `order` = 9999");
		}
		return $next;
	}

	public function upOutline($id,$order)
	{
		$previous = $this->query("SELECT max(`order`) as position FROM wrk_assignment_outline WHERE wrk_assignment_id = $id AND `order` < $order")->fetch()->position;
		if ($previous)
		{
			$this->query("UPDATE `wrk_assignment_outline` SET `order` = 9999 WHERE `wrk_assignment_id` = $id AND `order` = $order");
			$this->query("UPDATE `wrk_assignment_outline` SET `order` = $order WHERE `wrk_assignment_id` = $id AND `order` = $previous");
			$this->query("UPDATE `wrk_assignment_outline` SET `order` = $previous WHERE `wrk_assignment_id` = $id AND `order` = 9999");
		}
		return $previous;
	}

	public function downOutline($id,$order)
	{
		$next = $this->query("SELECT min(`order`) as position FROM wrk_assignment_outline WHERE wrk_assignment_id = $id AND `order` > $order")->fetch()->position;
		if ($next)
		{
			$this->query("UPDATE `wrk_assignment_outline` SET `order` = 9999 WHERE `wrk_assignment_id` = $id AND `order` = $order");
			$this->query("UPDATE `wrk_assignment_outline` SET `order` = $order WHERE `wrk_assignment_id` = $id AND `order` = $next");
			$this->query("UPDATE `wrk_assignment_outline` SET `order` = $next WHERE `wrk_assignment_id` = $id AND `order` = 9999");
		}
		return $next;
	}

	public function makeClone($id)
	{
		$this->query("INSERT INTO `wrk_assignment` (`name`,`description`,`resources`,`coworkers`,`subject`,`user_id`) SELECT `name`,`description`,`resources`,`coworkers`,`subject`,`user_id` FROM `wrk_assignment` WHERE id = $id");
		$newId =  $this->getInsertId();
		$this->query("INSERT INTO `wrk_assignment_goal` (`description`,`order`,`wrk_assignment_id`) SELECT `description`,`order`,$newId FROM `wrk_assignment_goal` WHERE `wrk_assignment_id` = $id");
		$this->query("INSERT INTO `wrk_assignment_outline` (`description`,`order`,`wrk_assignment_id`) SELECT `description`,`order`,$newId FROM `wrk_assignment_outline` WHERE `wrk_assignment_id` = $id");
		return $newId;
	}
}
