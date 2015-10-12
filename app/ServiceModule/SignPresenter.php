<?php

namespace App\ServiceModule\Presenters;

use Nette,
	App\Forms;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends \App\Presenters\BasePresenter
{
	/** @var \App\Forms\SignFormFactory @inject */
	public $factory;

	/** @var \App\Forms\RegistrationFormFactory @inject */
	public $registrationFactory;

	/** @var \App\Forms\RecoveryFormFactory @inject */
	public $recoveryFactory;

	/** @var \App\Forms\Recovery2FormFactory @inject */
	public $recovery2Factory;

	/** @var \App\Forms\VerificationFormFactory @inject */
	public $verificationFactory;

	/** @var \App\Model\Users @inject */
	public $model;

	/** @var \App\Model\Authenticator\LocalAuthenticator @inject */
	public $userModel;

	/** @var \Nette\Mail\IMailer @inject */
	public $mailer;

	/** @persistent */
  public $backlink = '';

  public $senderName = 'Michal Stehlí­k <rawac.st@gmail.com>';

  public function __construct()
	{
		parent::__construct();
		//$this->setTitle("");
	}

	protected function createComponentSignInForm()
	{
		$form = $this->factory->create($this->userModel);
		$form->onSuccess[] = function ($form)
		{
			$this->restoreRequest($this->backlink);
			$form->getPresenter()->redirect(':Front:Homepage:');
		};
		return $form;
	}

	protected function createComponentRegistrationForm()
	{
		$form = $this->registrationFactory->create($this->model);
		$form->onSuccess[] = array($this, 'registrationFormSucceeded');
		return $form;
	}

	protected function createComponentRecoveryForm()
	{
		$form = $this->recoveryFactory->create($this->model);
		$form->onSuccess[] = array($this, 'recoveryFormSucceeded');
		return $form;
	}

	protected function createComponentRecovery2Form()
	{
		$form = $this->recovery2Factory->create($this->userModel);
		$form->onSuccess[] = array($this, 'recovery2FormSucceeded');
		return $form;
	}

	protected function createComponentVerificationForm()
	{
		$form = $this->verificationFactory->create($this->userModel);
		$form->onSuccess[] = array($this, 'verificationFormSucceeded');
		return $form;
	}

	public function actionIn()
	{
		$this->setTitle("Přihlášení uživatele");
	}

	public function actionUp()
	{
		$this->setTitle("Registrace nového uživatele");
	}

	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Odhlášení proběhlo úspěšně.', 'success');
		$this->redirect('in');
	}

	public function registrationFormSucceeded($form,$values)
	{
		$password = $values->password;
		unset($values->password);
		unset($values->password2);
		try
		{
			$id = $this->model->insert($values);
			$this->model->update($id,array("enabled" => 0));
			$this->flashMessage("Uživatel byl úspěšně zaregistrován.","success");
			$this->userModel->add($id,$password);
			$random = \Nette\Utils\Random::generate();
			$this->userModel->setToken($id,$random);
      		$template = $this->createTemplate()->setFile(__DIR__ . '/../templates/emails/accountVerification.latte');
			$template->theme = "Ověření registrace";
			$template->firstname = $values->firstname;
      		$template->lastname = $values->lastname;
      		$template->gender = $values->gender;
      		$template->username = $values->email;
      		$template->verificationToken = $random;
      		$mail = new \Nette\Mail\Message();
      		$mail->setFrom($this->senderName)
        		->addTo($values->email)
         		->setSubject("Potvrzení registrace")
         		->setHtmlBody($template);
   			$this->mailer->send($mail);
   			$this->flashMessage("Na adresu " .$values->email . " byl zaslán ověřovací kód.","info");
		}
		catch (Exception $e)
		{
			$this->flashMessage("Registrace uživatele se nepodařila.","danger");
		}
		finally
		{
			$this->redirect("verification",array("username" => $values->email));
		}
	}

	public function actionVerification($username = null,$token = null)
	{
		$form = $this["verificationForm"];
		$form["username"]->setDefaultValue($username);
		$form["token"]->setDefaultValue($token);
	}

	public function verificationFormSucceeded($form,$values)
	{
		$username = $values->username;
		$token = $values->token;
		$userData = $this->model->getBy(array("email" => $username));
		if ($userData)
		{
			$id = $userData->id;
			try
			{
				$this->model->update($id,array("enabled" => 1));
				$this->userModel->removeToken($id);
				$this->userModel->setValidated($id);
				$this->flashMessage("Registrace je dokončena.","success");
				$this->redirect("in");
			}
			catch (Nette\Security\AuthenticationException $e) {
				$this->flashMessage("Během dokončení registrace došlo k chybě.","danger");
			}
		}
		$this->redirect("verification",array("username" => $username, "token" => $token));
	}

	public function recoveryFormSucceeded($form,$values)
	{
		$userData = $this->model->getBy(array("email" => $values->username));
		if (!$userData)
		{
			$this->flashMessage("Neznámé uživatelské jméno.","danger");
			$this->redirect("password");
		}
		else
		try
		{
			$random = \Nette\Utils\Random::generate();
			$id = $userData->id;
			$loginData = $this->userModel->query("SELECT * FROM login_local WHERE user_id = ".$id)->fetch();
			if(!$loginData) $this->userModel->add($id,\Nette\Utils\Random::generate());
			$this->userModel->setToken($id,$random);
      $template = $this->createTemplate()->setFile(__DIR__ . '/../templates/emails/passwordRecovery.latte');
      $template->username = $values->username;
			$template->theme = "Nastavení hesla";
      $template->verificationToken = $random;
      $mail = new \Nette\Mail\Message();
      $mail->setFrom($this->senderName)
         	 ->addTo($values->username)
         	 ->setSubject("Nastavení hesla")
         	 ->setHtmlBody($template);
      $this->mailer->send($mail);
      $this->flashMessage("Na adresu " .$values->username . " byl zaslán ověřovací kód.","info");
		}
		catch (Exception $e)
		{
			$this->flashMessage("Při odesílání ověřovacího kódu došlo k neznámé chybě.","danger");
		}
		finally
		{
			$this->redirect("recovery",array("username" => $values->username));
		}
	}

	public function actionRecovery($username = null,$token = null)
	{
		$form = $this["recovery2Form"];
		$form["username"]->setValue($username);
		$form["token"]->setValue($token);
	}

	public function recovery2FormSucceeded($form,$values)
	{
		$userData = $this->model->getBy(array("email" => $values->username));
		if (!$userData)
		{
			$this->flashMessage("Neznámé uživatelské jméno.","danger");
			$this->redirect("password");
		}
		else
		try
		{
			$id = $userData->id;
			$this->userModel->setPassword($id,$values->password);
			$this->userModel->removeToken($id);
      $this->flashMessage("Změna hesla proběhla úspěšně.","success");
		}
		catch (Exception $e)
		{
			$this->flashMessage("Při nastavování hesla došlo k chybě.","danger");
		}
		finally
		{
			$this->redirect("in");
		}
	}
}
