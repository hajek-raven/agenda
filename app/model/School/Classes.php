<?php
namespace App\Model\School;

class Classes extends \App\Model\Common\GridTableModel
{
	public function __construct(\DibiConnection $connection, \Nette\Security\User $user)
 	{
		parent::__construct($connection, "sch_class");
		$this->getSelection()->removeClause("SELECT");
		$this->getSelection()->select("sch_class.*, user.firstname AS teacher_firstname, user.lastname AS teacher_lastname, COUNT(*) as students")
			->leftJoin("user")->on("sch_class.teacher_id = user.id")
			->leftJoin("sch_student")->on("sch_class.id = sch_student.class_id")
			->groupBy("sch_class.id");
		$this->setPrimaryKey("sch_class.id");	
	}
	
	public function existsBakalari($code)
	{
		$sql = "SELECT id FROM " . $this->getTableName() . " WHERE `bakalari_code` = '$code'";
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
