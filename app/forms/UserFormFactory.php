<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class UserFormFactory extends Nette\Object
{
	protected $model;

	/**
	 * @return Form
	 */
	public function create($model)
	{
		$form = new BaseForm();
		$this->model = $model;
  	$form->addHidden('id');
		$form->addText('title', 'Titul');
  	$form->addText('firstname', 'Jméno')->setRequired("Vyplňte jméno");
    $form->addText('lastname', 'Příjmení')->setRequired("Vyplňte příjmení");
		$form->addText('title_after', 'Titul za jménem');
    $form->addRadioList('gender','Pohlaví',array("M" => "Muž", "F" => "Žena"))->setRequired("Vyplňte pohlaví");
    $form->addText('birthdate', 'Datum narození')->setRequired("Vyplňte datum narození")->setType('date');
    $form->addText('email', 'Kontaktní email')->setRequired("Vyplňte kontaktní email")->setType('email')->setAttribute('placeholder', 'nekdo@nekde.cz');;
    $form->addText('phone', 'Telefon')->setType('phone')->setAttribute('placeholder', '420xxxxxxxxx');
    $form->onValidate[] = array($this, 'validityCheck');
    $form->addSubmit('send', 'Uložit');
		return $form;
	}

	public function validityCheck($form)
	{
		$values = $form->getValues();
		$data = $this->model->getSelection()->where(array("email" => $values->email))->fetch();
		if (($data && !$values->id) || ($data && $values->id && ($data["id"] != $values->id)))
			$form->addError('Tento email má registrovaný jiný uživatel.');
		if (empty($values->phone)) unset($values->phone);
	}
}
