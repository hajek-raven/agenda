<?php
use Nette\Application\UI\Control;

class SortableList extends Control
{
  protected $model;
  protected $retrieveData = null;

  public function __construct($model)
  {
    $this->model = $model;
  }

  public function setRetrieveData($methodName)
  {
    $this->retrieveData = $methodName;
  }

  protected function getData()
  {
    if ($this->retrieveData)
    {
      return call_user_func_array();
    }
  }

  public function render()
  {
    $template = $this->template;
    $template->setFile(__DIR__ . '/SortableList.latte');
    // vložíme do šablony nějaké parametry
    $template->data = $value;
    $template->render();
  }
}
