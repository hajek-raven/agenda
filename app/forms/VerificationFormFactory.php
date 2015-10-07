<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class VerificationFormFactory extends Nette\Object
{
	protected $modelUserManager;

	/**
	 * @return Form
	 */
	public function create($modelUserManager)
	{
		$this->modelUserManager = $modelUserManager;
		$form = new BaseForm();
  		$form->addText('username', 'Uživatelské jméno')->setRequired("Vyplňte přihlašovací jméno uživatele (emailovou adresu).");
  		$form->addText('token', 'Ověřovací kód')->setRequired("Vyplňte ověřovací kód.");
      	$form->addSubmit('send', 'Potvrdit');
      	$form->onValidate[] = array($this, 'validityCheck');
		return $form;
	}

	public function validityCheck($form)
	{
		$values = $form->getValues();
		try {
			if ($this->modelUserManager->verifyToken($values->username, $values->token))
			{
				// do nothing, just submit
			}
		}
		catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}
}
