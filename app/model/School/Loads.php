<?php
namespace App\Model\School;

class Loads extends \App\Model\Common\GridTableModel
{
	public function __construct(\DibiConnection $connection, \Nette\Security\User $user)
 	{
		parent::__construct($connection, "sch_load");
		$this->getSelection()->removeClause("SELECT");
		$this->getSelection()->select("sch_load.*, sch_subject.name AS subject_name, sch_group.name AS group_name, user.firstname AS teacher_firstname, user.lastname AS teacher_lastname, sch_class.shortname AS class_name, user.id AS user_id")
			->leftJoin("sch_subject")->on("sch_load.sch_subject_id = sch_subject.id")
			->leftJoin("sch_group")->on("sch_load.sch_group_id = sch_group.id")
			->leftJoin("sch_teacher")->on("sch_load.sch_teacher_id = sch_teacher.user_id")
			->leftJoin("user")->on("sch_teacher.user_id = user.id")
			->leftJoin("sch_class")->on("sch_group.sch_class_id = sch_class.id")
			;
		$this->setPrimaryKey("sch_load.id");	
	}
	
	public function purge()
	{
		$this->query("DELETE FROM `{$this->getTableName()}`");
		$this->query("ALTER TABLE `{$this->getTableName()}` AUTO_INCREMENT = 1");
	}
}
