<?php
namespace App\Model;

class Users extends Common\GridTableModel
{
	public function __construct(\DibiConnection $connection)
 	{
		parent::__construct($connection, "user");
		$this->selection->leftJoin("login_local")->on("user.id = login_local.user_id");
  }

	public function asArray()
	{
		$result = array();
		$sql = "SELECT user.id, firstname, lastname FROM " . $this->getTableName() . " ORDER BY `lastname`, `firstname`";
		$data = $this->query($sql)->fetchAll();
		foreach($data as $record)
		{
			$result[$record->id] = $record->lastname . ", " .$record->firstname;
		}
		return $result;
	}

	public function fromGroupAsArray($group, array $add = array())
	{
		$result = array();
		$sql = "SELECT user.id, firstname, lastname FROM " . $this->getTableName() . " LEFT JOIN membership ON membership.user_id = user.id WHERE membership.group_id = " . $group;
		if ($add)
		{
			$list = implode(", ",$add);
			$sql .= " OR user.id IN(".$list.")";
		}
		$sql .= " ORDER BY `lastname`, `firstname`";
		$data = $this->query($sql)->fetchAll();
		foreach($data as $record)
		{
			$result[$record->id] = $record->lastname . ", " .$record->firstname;
		}
		return $result;
	}

	public function fromGroupRoleAsArray($groupName, $add = array())
	{
		$groupData = $this->query("SELECT id FROM `group` WHERE role_name = \"" . $groupName ."\"")->fetch();
		if($groupData)
		{
			return($this->fromGroupAsArray($groupData->id,$add));
		}
		else
		{
			return array();
		}
	}
}
