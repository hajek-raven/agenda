<?php

namespace App\Model\Authenticator;

use Nette,
    Nette\Security;

class SimpleAuthenticator extends CredentialsAuthenticator
{
  /** @var Nette\Security\User */

  private $loginData = array("st" => "beruska");
  private $identityData = array("st" => 1);

  public function __construct(Nette\Security\User $user, \App\Model\Users $usersModel, \App\Model\Membership $membershipModel)
  {
    parent::__construct($user, $usersModel, $membershipModel);
  }

  public function authenticate(array $credentials)
  {
    list($username,$password) = $credentials;
    if(!isset($this->loginData[$username]))
    {
      throw new \Nette\Security\AuthenticationException('Neznámé jméno uživatele.', self::IDENTITY_NOT_FOUND);
    }
    elseif ($this->loginData[$username] != $password)
    {
      throw new \Nette\Security\AuthenticationException('Nesprávné heslo.', self::INVALID_CREDENTIAL);
    }
    $identity = $this->buildIdentity($this->identityData[$username]);
    $active = $identity->getData()["active"];
    if(!$active) throw new \Nette\Security\AuthenticationException('Tento účet není aktivní.', self::INACTIVE);
    $this->user->login($identity);
  }
}
