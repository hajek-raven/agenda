<?php
namespace App\Model\School;

class Groups extends \App\Model\Common\GridTableModel
{
	public function __construct(\DibiConnection $connection, \Nette\Security\User $user)
 	{
		parent::__construct($connection, "sch_group");
		$this->getSelection()->removeClause("SELECT");
		$this->getSelection()->select("sch_group.*, sch_class.shortname AS class, count(user_id) AS members")
            ->join("sch_class")->on("sch_group.sch_class_id = sch_class.id")
            ->leftJoin("sch_group_membership")->on("sch_group_membership.sch_group_id = sch_group.id")
            ->groupBy("sch_group.id");
		$this->setPrimaryKey("sch_group.id");	
	}
	
	public function purge()
	{
		$this->query("DELETE FROM `{$this->getTableName()}`");
		$this->query("ALTER TABLE `{$this->getTableName()}` AUTO_INCREMENT = 1");
	}
}
