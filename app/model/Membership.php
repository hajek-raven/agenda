<?php
namespace App\Model;

class Membership extends Common\GridTableModel
{
	public function __construct(\DibiConnection $connection)
 	{
		parent::__construct($connection, "membership");
    	$this->selection->leftJoin("user")->on("membership.user_id = user.id");
  }

	public function delete($id)
	{
		/// do nothing on purpose
	}

	public function out($user,$group)
	{
		$this->query("DELETE FROM " . $this->getTableName() . " WHERE user_id = $user AND group_id = $group");
	}

	public function in($user,$group)
	{
		$sel = $this->query("SELECT * FROM membership WHERE user_id = $user AND group_id = $group")->fetch();
		if(!$sel) 
		{
			$this->query("INSERT INTO " . $this->getTableName() . " (`user_id` ,`group_id`) VALUES ($user, $group)");
		}
	}
	
	public function inNamedGroup($user,$groupName)
	{
		$groupId = $this->query("SELECT id FROM `group` WHERE role_name = \"$groupName\"")->fetch();
		if ($groupId)
		{
			$this->in($user,$groupId->id);
		}
	}
	
	public function userIsMember($userId)
	{
		return $this->query("SELECT * FROM `membership` JOIN `group` ON membership.`group_id` = `group`.id WHERE membership.user_id = ".$userId." ORDER BY name")->fetchAll();
	}
	
	public function userIsNotMemberOfGroupsAsArray($userId)
	{
		$return = array();
		$data = $this->query("SELECT id,name,COALESCE(SUM(membership.user_id=$userId),0) AS member FROM `group` LEFT JOIN `membership` ON `group`.`id` = `membership`.`group_id` GROUP BY `group`.`id` HAVING member = 0 ORDER BY name")->fetchAll();
		foreach($data as $record)
		{
			$return[$record->id] = $record->name;
		}
		return $return;
	}
	
	public function usersAreNotMembersOfGroupAsArray($groupId)
	{
		$return = array();
		$data = $this->query("SELECT id,lastname,firstname,COALESCE(SUM(membership.group_id=$groupId),0) AS member FROM `user` LEFT JOIN `membership` ON `user`.`id` = `membership`.`user_id` GROUP BY `user`.`id` HAVING member = 0 ORDER BY lastname,firstname")->fetchAll();
		foreach($data as $record)
		{
			$return[$record->id] = $record->lastname .", ".$record->firstname;
		}
		return $return;
	}
}
