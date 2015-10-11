<?php
namespace App\Grids;

class baseGrid extends \Grido\Grid
{
 protected $user;
 protected $settings;

 public static $booleanReplacements = array("" => "", "1" => "<span class=\"icon-picture yes\">Ano</span>", "0" => "<span class=\"icon-picture no\">Ne</span>");
 public static $booleanFilters = array("" => "", "1" => "Ano", "0" => "Ne");
 public static $genderReplacements = array("" => "", "M" => "Muž", "F" => "Žena");
 public static $genderFilters = array("" => "", "M" => "Muž", "F" => "Žena");

 public function __construct($parent, $name)
 {
  parent::__construct($parent, $name);
  $this->user = $parent->user->getIdentity();
  $this->settings = $this->user->settings;
  $this->setTranslator(new \Grido\Translations\FileTranslator('cs'));
  //$this->setDefaultPerPage($this->settings["table_rows"]);
  $this->tablePrototype = \Nette\Utils\Html::el('table');
  $this->tablePrototype->id($this->getName())->class[] = 'table table-striped table-hover';
  $this->setExport();
 }
}
