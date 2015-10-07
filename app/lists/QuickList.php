<?php
namespace App\Lists;

use Nette\Application\UI\Control;

class QuickList extends Control
{
  protected $model;
  protected $getDataCallback;
  protected $getDataParameters;
  protected $editable = false;

  public function __construct($presenter,$name,$model)
	{
		parent::__construct($presenter,$name);
    $this->model = $model;
	}

  public function setGetDataMethod($name, $parameters)
  {
    $this->getDataCallback = $name;
    $this->getDataParameters = $parameters;
  }

  protected function getData(array $params)
  {
    $data = call_user_func_array(array($this->model,$this->getDataCallback), $params);
    return $data;
  }

  public function setEditable($value)
  {
    $this->editable = $value;
  }

  public function handleRefresh()
  {
    $this->redrawControl();
    $this->flashMessage("Reload","info");
  }

  public function render()
  {
    $template = $this->template;
    $template->setFile(__DIR__ . '/quickList.latte');
    $this->template->data = $this->getData($this->getDataParameters);
    $this->template->editable = $this->editable;
    $template->render();
  }
}
