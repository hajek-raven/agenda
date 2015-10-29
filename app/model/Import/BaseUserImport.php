<?php
namespace App\Model\Import;

abstract class BaseUserImport extends \Nette\Object
{
  protected $userModel;
  protected $groupModel;

  public function __construct(\App\Model\Users $userModel, \App\Model\Groups $groupModel)
  {
    $this->userModel = clone $userModel;
    $this->groupModel = clone $groupModel;
  }
  
  public function query($sql)
  {
	  return $this->userModel->query($sql);
  }
  
  public function importRecord($data)
  {
	  
  }
}