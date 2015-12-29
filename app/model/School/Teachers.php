<?php
namespace App\Model\School;

class Teachers extends \App\Model\Common\GridTableModel
{
	public function __construct(\DibiConnection $connection, \Nette\Security\User $user)
 	{
		parent::__construct($connection, "sch_teacher");
		$this->getSelection()->removeClause("SELECT");
		$this->getSelection()->select("sch_teacher.*, user.firstname, user.lastname, user.title, user.title_after, user.email, user.phone, user.gender, SUM(sch_load.hours) AS hours")
			->join("user")->on("sch_teacher.user_id = user.id")
			->leftJoin("sch_load")->on("sch_load.sch_teacher_id = sch_teacher.user_id")
			->groupBy("sch_teacher.user_id");
		$this->setPrimaryKey("sch_teacher.user_id");	
	}
	
	public function existsBakalari($code)
	{
		$sql = "SELECT user_id FROM " . $this->getTableName() . " WHERE `bakalari_code` = '$code'";
		$data = $this->query($sql)->fetch();
		return $data;
	}
	
	public function invalidate()
	{
		$sql = "UPDATE " . $this->getTableName() . " set `invalidated` = '1'";
		$this->query($sql);
	}
	
	public function removeUnused()
	{
		$sql = "DELETE FROM " . $this->getTableName() . " WHERE `invalidated` = '1'";
		$this->query($sql);
	}
}
