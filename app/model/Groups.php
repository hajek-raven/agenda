<?php
namespace App\Model;

class Groups extends Common\GridTableModel
{
	public function __construct(\DibiConnection $connection, \Nette\Security\User $user)
 	{
		parent::__construct($connection, "group");
		$this->getSelection()->removeClause("SELECT");
		$this->getSelection()->leftJoin("user")->on("group.user_id = user.id")->leftJoin("membership")->on("group.id = membership.group_id")
			->groupBy("group.id")
			->select("`group`.*, count(*) AS members, user.firstname, user.lastname, COALESCE(SUM(membership.user_id={$user->id}),0) AS member");
		$this->setPrimaryKey("group.id");	
  }
}
