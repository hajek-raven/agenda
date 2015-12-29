<?php
namespace App\Model\School;

class Students extends \App\Model\Common\GridTableModel
{
	public function __construct(\DibiConnection $connection, \Nette\Security\User $user)
 	{
		parent::__construct($connection, "sch_student");
		$this->getSelection()->removeClause("SELECT");
		$this->getSelection()->select("sch_student.*, user.firstname, user.lastname, user.title, user.title_after, user.email, user.phone, user.gender, sch_class.shortname AS class")
			->join("user")->on("sch_student.user_id = user.id")
			->leftJoin("sch_class")->on("sch_student.class_id = sch_class.id");
		$this->setPrimaryKey("sch_student.user_id");	
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
