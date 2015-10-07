<?php

namespace App\Model\Authenticator;

use Nette,
    Nette\Security;

abstract class BaseAuthenticator extends Nette\Object
{
  /** @var Nette\Security\User */
  protected $user;
  /** @var \App\Model\Users */
  protected $usersModel;
  /** @var \App\Model\Groups */
  protected $membershipModel;

  public function getUser()
  {
    return $this->user;
  }

  public function __construct(Nette\Security\User $user, \App\Model\Users $usersModel, \App\Model\Membership $membershipModel)
  {
    $this->user = $user;
    $this->usersModel = $usersModel;
    $this->membershipModel = $membershipModel;
  }

  public function authenticate(array $credentials)
  {
    $identity = $this->buildIdentity(-1);
    $this->user->login($identity);
  }

  protected function buildIdentity($userID)
  {
    $userData = $this->usersModel->get($userID);
    if (!$userData) throw new \Nette\Security\AuthenticationException('Neexistují odpovídající data o uživateli.', self::FAILURE);
    unset($userData->id);
    $membershipData = $this->membershipModel->findBy(array("membership.user_id" => $userID))->fetchAll();
    $roles = array();
    foreach($membershipData as $record)
    {
      $roles[] = $record->role_name;
    }
    return new \Nette\Security\Identity($userID,$roles,$userData);
  }

  /** Exception error code */
  const IDENTITY_NOT_FOUND = 1,
        INVALID_CREDENTIAL = 2,
        FAILURE = 3,
        NOT_APPROVED = 4,
        INACTIVE = 5;
}
