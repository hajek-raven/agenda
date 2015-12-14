<?php
namespace App\Model\School;

class Subjects extends \App\Model\Common\GridTableModel
{
	public function __construct(\DibiConnection $connection, \Nette\Security\User $user)
 	{
		parent::__construct($connection, "sch_subject");	
  }
}
