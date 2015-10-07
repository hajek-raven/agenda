<?php

namespace App\Forms\Work;

use Nette,
	Nette\Application\UI\Form;


class SetFormFactory extends Nette\Object
{
	protected $model;

	/**
	 * @return Form
	 */
	public function create($model)
	{
		$form = new \App\Forms\BaseForm();
		$this->model = $model;
  	$form->addHidden('id');
  	$form->addText('name', 'Název')->setRequired("Vyplňte název");
		$templates = array("dmp","drp","ap");
		$form->addSelect('template', 'Šablona')->setItems($templates, FALSE)->setRequired("Nastavte název šablony pro tisk");
    $form->addCheckbox('active', 'Aktivní');
		$form->addText('max_grade', 'Nejvyšší možná známka')->setType('number')->setAttribute('placeholder', 'pravděpopodobně 4 nebo 5')->setRequired("Nastavte nejhorší možnou známku.");
    $form->addSubmit('send', 'Uložit');
		return $form;
	}
}
