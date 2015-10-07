<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class RegistrationFormFactory extends Nette\Object
{

	protected $model;
	/**
	 * @return Form
	 */
	public function create($model)
	{
		$this->model = $model;
		$form = new BaseForm();
  		$form->addHidden('id');
  		$form->addText('firstname', 'Jméno')->setRequired("Vyplňte jméno");
      	$form->addText('lastname', 'Příjmení')->setRequired("Vyplňte příjmení");
      	$form->addRadioList('gender','Pohlaví',array("M" => "Muž", "F" => "Žena"))->setRequired("Vyplňte pohlaví");
      	$form->addText('birthdate', 'Datum narození')->setRequired("Vyplňte datum narození")->setType('date')->setValue((new \Nette\DateTime())->format("Y-m-d"));
      	$form->addText('email', 'Kontaktní email')->setRequired("Vyplňte kontaktní email")->setType('email')->setAttribute('placeholder', 'nekdo@nekde.cz');
      	$form->addPassword('password', 'Heslo')->setRequired("Vyplňte heslo");
      	$form->addPassword('password2', 'Heslo pro kontrolu')->setRequired("Vyplňte heslo");
      	$form->addSubmit('send', 'Uložit');
      	$form->onValidate[] = array($this, 'validityCheck');
		return $form;
	}

	public function validityCheck($form)
	{
		$values = $form->getValues();
		if ($values->password != $values->password2)
			$form->addError('Obě varianty hesla musí být stejné.');
		$bdate = new \Nette\DateTime($values->birthdate);
		$diff = $bdate->diff(new \Nette\DateTime());
		$diff = $diff->format('%d');
		if ($diff < 7)
			$form->addError('Uživatelé by měli být starší než jeden týden. ' . $diff . ' dní je příliš málo.');
		$data = $this->model->getSelection()->where(array("email" => $values->email))->fetch();
		if ($data)
			$form->addError('Email ' . $values->email . ' je již používán.');
	}
}
