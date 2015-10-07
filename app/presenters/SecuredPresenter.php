<?php

namespace App\Presenters;

use Nette,
	App\Model;

abstract class SecuredPresenter extends BasePresenter
{
	public function __construct()
	{
		parent::__construct();	
	}	

    protected function startup()
    {
        parent::startup();

        $user = $this->getUser();

        if (!$user->isLoggedIn()) {
            if ($user->getLogoutReason() === \Nette\Security\User::INACTIVITY) {
                $this->flashMessage('Uplynula doba neaktivity! Byl jste z bezpečnostních důvodů automaticky odhlášen.', 'warning');
            }
            else
            {
                $this->flashMessage('K přístupu k této činnosti musíte být přihlášen.', 'warning');
            }

            $backlink = $this->storeRequest();
            $this->redirect(':Service:Sign:In', array('backlink' => $backlink));

        } else {
            if (!$user->isAllowed($this->name, $this->action)) {
                /*
                $this->flashMessage('K této činnosti vaše oprávnění nepostačují. Přístup odepřen.', 'warning');
                $backlink = $this->storeRequest();
                $this->redirect(':Service:Sign:In', array('backlink' => $backlink));*/
                throw new Nette\Application\ForbiddenRequestException;
            }
        }
    }	
}