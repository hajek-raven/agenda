<?php

namespace App\Forms\Work;

use Nette,
	Nette\Application\UI\Form;


class AssignmentFormFactory extends Nette\Object
{
	protected $model;
  protected $assignments = array();
  protected $students = array();
  protected $sets = array();

	/**
	 * @return Form
	 */
	public function create($model, array $assignments, array $students, array $sets)
	{
		$form = new \App\Forms\BaseForm();
		$this->model = $model;
		$this->assignments = $assignments;
		$this->students = $students;
		$this->sets = $sets;
  	$form->addHidden('id');
  	$form->addText('name', 'Název (ponechte prázdné, pokud má být stejné jako název zadání)');
    $form->addSelect('wrk_assignment_id', 'Námět',$assignments)->setRequired("Vyberte připravený námět práce")->setPrompt('Zvolte námět');
    $form->addSelect('user_id', 'Autor práce', $students)->setPrompt('Zvolte studenta')->setRequired("Práce musí být přidělena studentovi.");
    $form->addSelect('wrk_set_id', 'Sada prací', $sets)->setPrompt('Zvolte sadu')->setRequired("Práce musí patřit do nějaké sady.");
    $form->addText('class', 'Zkratka třídy')->setRequired("Vyplňte zkratku třídy");
    $form->addText('consultant', 'Jméno případného konzultanta');
		$form->addText('year', 'Školní rok (začátek)');
		$form->addSubmit('send', 'Uložit');
		return $form;
	}
}
