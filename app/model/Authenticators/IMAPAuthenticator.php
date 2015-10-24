<?php

namespace App\Model\Authenticator;

use Nette,
    Nette\Security;

class IMAPAuthenticator extends CredentialsAuthenticator
{
  /** @var Nette\Security\User */
  private $imapLoginModel;

  public function __construct(Nette\Security\User $user, \App\Model\Users $usersModel, \App\Model\Membership $membershipModel, \DibiConnection $connection)
  {
    parent::__construct($user, $usersModel, $membershipModel);
    $this->imapLoginModel = new ImapUserManager($connection);
  }

  public function authenticate(array $credentials)
  {
    list($username,$password) = $credentials;
    $loginData = $this->imapLoginModel->getBy(array("username" => $username));
    $mbox = imap_open("{localhost:993/ssl/novalidate-cert}INBOX",$username, $password, OP_HALFOPEN | OP_SILENT);
    imap_alerts();
    imap_errors();    
    if(!$loginData)
    {
      throw new \Nette\Security\AuthenticationException('Neznámé jméno uživatele.', self::IDENTITY_NOT_FOUND);
    }
    elseif (!$mbox)
    {
      throw new \Nette\Security\AuthenticationException('Nesprávné heslo.', self::INVALID_CREDENTIAL);
    }
    $identity = $this->buildIdentity($this->identityData[$username]);
    $enabled = $identity->getData()["enabled"];
    if(!$enabled) throw new \Nette\Security\AuthenticationException('Tento účet je zablokovaný.', self::INACTIVE);
    $this->user->login($identity);
  }
}

class ImapUserManager extends \App\Model\Common\TableModel
{
  public function __construct($connection)
  {
    parent::__construct($connection,"login_imap");
    $this->setPrimaryKey("user_id");
  }
}
