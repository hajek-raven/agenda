<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form,
	Nette\Security\User,
	Nette\Security\Identity;


class SignFormFactory extends Nette\Object
{
	/** @var User */
	//private $authenticationModel;
	private $authenticators;

	public function __construct(array $authenticators = array())
	// \App\Model\Authenticator\CredentialsAuthenticator $authenticationModel
	{
		//$this->authenticationModel = $authenticationModel;
		$this->authenticators = $authenticators;
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
		if (empty($this->authenticators))
		{
			$form->addError("Neexistuje žádný authenticator, proti kterému by bylo možné přihlašovací údaje ověřit.");
		}
		else
		{
			$successfull = false;
			$errors = array();
			foreach ($this->authenticators as $index => $authenticator)
			{
				try
				{					
					$authenticator->authenticate(array($values->username, $values->password));
					$successfull = $index;
					break;
				}
				catch (Nette\Security\AuthenticationException $e)
				{
					$className = explode("\\",get_class($authenticator));
					$errors[] = end($className) . ": " . $e->getMessage();
				}
			}
			if($successfull !== false)
			{
				$succesfullAuthenticator = $this->authenticators[$successfull];
				if ($values->remember)
				{
					$succesfullAuthenticator->getUser()->setExpiration('14 days', FALSE);
				}
				else
				{
					$succesfullAuthenticator->getUser()->setExpiration('20 minutes', TRUE);
				}			
			}
			else
			{
				foreach ($errors as $error)
				{
					$form->addError($error);
				}
			}			
		}
		/*
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
		*/
	}

}
