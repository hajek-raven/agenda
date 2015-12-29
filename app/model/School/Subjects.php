<?php
namespace App\Model\School;

class Subjects extends \App\Model\Common\GridTableModel
{
	public function __construct(\DibiConnection $connection, \Nette\Security\User $user)
 	{
		parent::__construct($connection, "sch_subject");
		$this->getSelection()->removeClause("SELECT");
		$this->getSelection()->select("sch_subject.*,SUM(hours) AS hours")
			->leftJoin("sch_load")->on("sch_load.sch_subject_id = sch_subject.id")
			->groupBy("sch_subject.id");
		$this->setPrimaryKey("sch_subject.id");	
  }
}
