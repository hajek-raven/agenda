<?php
namespace App\Model;

class Membership extends Common\GridTableModel
{
	public function __construct(\DibiConnection $connection)
 	{
		parent::__construct($connection, "membership");
    $this->selection->leftJoin("group")->on("membership.group_id = group.id");
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
		$this->query("INSERT INTO " . $this->getTableName() . " (`user_id` ,`group_id`) VALUES ($user, $group)");
	}
}
