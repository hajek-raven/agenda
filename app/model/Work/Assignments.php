<?php
namespace App\Model\Work;

class Assignments extends \App\Model\Common\GridTableModel
{
	public function __construct(\DibiConnection $connection)
 	{
		parent::__construct($connection, "wrk_work");
		$this->getSelection()->removeClause("SELECT");
		$this->getSelection()->select("wrk_work.*, user.firstname, user.lastname, wrk_set.name as set_name")
			->leftJoin("user")->on("wrk_work.user_id = user.id")
			->leftJoin("wrk_set")->on("wrk_work.wrk_set_id = wrk_set.id");
		$this->setPrimaryKey("wrk_work.id");
  }

	public function getAssignedRoles($work)
	{
		$workData = $this->query("SELECT * FROM wrk_work WHERE id = ".$work)->fetch();
		if ($workData)
		{
			$result = array();
			$roleInSet = $this->query("SELECT * FROM wrk_set_role WHERE wrk_set_id = ".$workData->wrk_set_id)->fetchAll();
			foreach($roleInSet as $setRole)
			{
				$result[$setRole->id] = (array)$setRole;
			}
			foreach($result as $role)
			{
				$result[$role["id"]]["evaluators"] = array();
				$assignedRole = $this->query("SELECT * FROM wrk_work_role JOIN user ON user.id = wrk_work_role.user_id WHERE wrk_set_id = " . $workData->wrk_set_id . " AND wrk_set_role_id = " .  $role["id"] . " AND wrk_work_id = ".$work)->fetchAll();
				foreach ($assignedRole as $ar)
				{
					$result[$role["id"]]["evaluators"][$ar["user_id"]] = (array)$ar;
				}
			}
			return($result);
		}
		return false;
	}

	public function getAssignedRolesForPrintApplication($work)
	{
		$workData = $this->query("SELECT * FROM wrk_work WHERE id = ".$work)->fetch();
		if ($workData)
		{
			$result = array();
			$roleInSet = $this->query("SELECT * FROM wrk_set_role WHERE wrk_set_id = ".$workData->wrk_set_id . " AND printed_application = 1")->fetchAll();
			foreach($roleInSet as $setRole)
			{
				$result[$setRole->id] = (array)$setRole;
			}
			foreach($result as $role)
			{
				$result[$role["id"]]["evaluators"] = array();
				$assignedRole = $this->query("SELECT * FROM wrk_work_role JOIN user ON user.id = wrk_work_role.user_id WHERE wrk_set_id = " . $workData->wrk_set_id . " AND wrk_set_role_id = " .  $role["id"] . " AND wrk_work_id = ".$work)->fetchAll();
				foreach ($assignedRole as $ar)
				{
					$result[$role["id"]]["evaluators"][$ar["user_id"]] = (array)$ar;
				}
			}
			return($result);
		}
		return false;
	}
}
