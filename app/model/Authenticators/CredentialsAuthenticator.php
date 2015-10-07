<?php

namespace App\Model\Authenticator;

use Nette,
    Nette\Security;

abstract class CredentialsAuthenticator extends BaseAuthenticator
{
  public function __construct(Nette\Security\User $user, \App\Model\Users $usersModel, \App\Model\Membership $membershipModel)
  {
    parent::__construct($user, $usersModel, $membershipModel);
  }

  public function authenticate(array $credentials)
  {
    list($username,$password) = $credentials;

    $this->user->login($this->buildIdentity(1));
  }
}
