<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form,
	Nette\Security\User,
	Nette\Security\Identity;


class SignFormFactory extends Nette\Object
{
	/** @var User */
	private $authenticationModel;

	public function __construct(\App\Model\Authenticator\CredentialsAuthenticator $authenticationModel)
	{
		$this->authenticationModel = $authenticationModel;
	}

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new BaseForm();
		$form->addText('username', 'Uživatelské jméno:')->setRequired('Zadejte své uživatelské jméno.');
		$form->addPassword('password', 'Heslo:')->setRequired('Zadejte heslo.');
		$form->addCheckbox('remember', 'Dlouhodobé přihlášení');
		$form->addSubmit('send', 'Přihlásit');
		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded($form, $values)
	{
		if ($values->remember)
		{
			$this->authenticationModel->getUser()->setExpiration('14 days', FALSE);
		}
		else
		{
			$this->authenticationModel->getUser()->setExpiration('20 minutes', TRUE);
		}
		try
		{
			$this->authenticationModel->authenticate(array($values->username, $values->password));
		}
		catch (Nette\Security\AuthenticationException $e)
		{
			$form->addError($e->getMessage());
		}
	}

}
