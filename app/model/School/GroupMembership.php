<?php
namespace App\Model\School;

class GroupMembership extends \App\Model\Common\GridTableModel
{
	public function __construct(\DibiConnection $connection, \Nette\Security\User $user)
 	{
		parent::__construct($connection, "sch_group_membership");
		$this->getSelection()->removeClause("SELECT");
		$this->getSelection()->select("user.*, user_id AS user_id")
            ->join("user")->on("sch_group_membership.user_id = user.id");
		$this->setPrimaryKey("sch_group_membership.user_id");	
	}
	
	public function purge()
	{
		$this->query("DELETE FROM `{$this->getTableName()}`");
		$this->query("ALTER TABLE `{$this->getTableName()}` AUTO_INCREMENT = 1");
	}
}
