<?php
namespace App\Model\Common;

abstract class BaseModel extends \Nette\Object
{
  protected $selection;
  protected $connection;
  protected $initialSelection;

  public function __construct(\DibiConnection $connection,$selection)
  {
    $this->connection = $connection;
    $this->selection = $selection;
    $this->initialSelection = $selection;
  }

  public function getConnection()
  {
    return $this->connection;
  }

  public function setSelection($selection) // obsolete
  {
    $this->selection = $selection;
  }

  public function getSelection()
  {
    return $this->selection;
  }

  public function getClonedSelection()
  {
    return clone ($this->initialSelection);
  }

  public function query($sql)
  {
    return $this->getConnection()->query($sql);
  }

  public function findBy(array $by)
  {
    $result = $this->getSelection();
    foreach($by as $col => $val)
    {
      $result = $result->where("[$col] = %s", $val);
    }
    return $result;
  }

  public function getBy(array $by)
  {
    return $this->findBy($by)->fetch();
  }

  public function fetch()
  {
    return $this->getSelection()->fetch();
  }

  public function fetchAll()
  {
    return $this->getSelection()->fetchAll();
  }

  public function getCount()
  {
    $selection = clone ($this->getSelection());
    return $selection->count();
  }

  public function getInsertId()
  {
    return $this->getConnection()->insertId();
  }
}
