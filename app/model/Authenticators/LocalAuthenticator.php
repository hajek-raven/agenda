<?php

namespace App\Model\Authenticator;

use Nette,
    Nette\Security,
	  Nette\Utils\Strings,
    Nette\Security\Passwords;

final class LocalAuthenticator extends CredentialsAuthenticator
{
  /** @var Nette\Security\User */

  private $localLoginModel;

  public function __construct(Nette\Security\User $user, \App\Model\Users $usersModel, \App\Model\Membership $membershipModel, \DibiConnection $connection)
  {
    parent::__construct($user, $usersModel, $membershipModel);
    $this->localLoginModel = new LocalUserManager($connection);
  }

  public function authenticate(array $credentials)
  {
    list($username,$password) = $credentials;
    $loginData = $this->localLoginModel->getBy(array("user.email" => $username));
    if(!$loginData)
    {
      throw new \Nette\Security\AuthenticationException('Neznámé jméno uživatele.', self::IDENTITY_NOT_FOUND);
    }
    elseif (!Passwords::verify($password, $loginData["password"]))
    {
      throw new \Nette\Security\AuthenticationException('Nesprávné heslo.', self::INVALID_CREDENTIAL);
    }
    elseif (Passwords::needsRehash($loginData["password"]))
    {
      $this->localLoginModel->update($loginData->user_id, array("password" => Passwords::hash($password)));
    }
    $identity = $this->buildIdentity($loginData->user_id);
    $enabled = $identity->getData()["enabled"];
    if(!$enabled) throw new \Nette\Security\AuthenticationException('Tento účet je zablokovaný.', self::INACTIVE);
    $this->user->login($identity);
  }

  public function add($id,$password)
  {
    $this->localLoginModel->delete($id);
    $this->localLoginModel->dumbInsert(array("user_id" => $id, "password" => Passwords::hash($password)));
  }

  public function setPassword($id,$newPassword)
  {
    $this->localLoginModel->update($id,array("password" => Passwords::hash($newPassword)));
  }

  public function setToken($id, $token)
	{
		$hashed = \Nette\Security\Passwords::hash($token);
		$now = new \Nette\DateTime(self::TOKEN_VALIDITY);
		$this->localLoginModel->update($id,array("token" => $hashed, "token_expiration" => $now->format('Y-m-d H:i:s')));
	}

  public function verifyToken($username, $token)
  {
    $now = new \Nette\DateTime();
		//$hashed = \Nette\Security\Passwords::hash($token);
    $row = $this->localLoginModel->query("SELECT * FROM `login_local` JOIN `user` ON `user`.`id` = `login_local`.`user_id` WHERE `user`.`email` = \"$username\"")->fetch();
    if (!$row) {
			throw new Nette\Security\AuthenticationException('Neznámé uživatelské jméno.', self::IDENTITY_NOT_FOUND);
		} elseif (!Passwords::verify($token, $row["token"])) {
			throw new Nette\Security\AuthenticationException('Neodpovídající kód.', self::INVALID_CREDENTIAL);
		} elseif ($now > (new \Nette\DateTime($row["token_expiration"]))) {
			throw new Nette\Security\AuthenticationException('Platnost kódu již vypršela.', self::INVALID_CREDENTIAL);
		}
    return true;
  }

  public function removeToken($id)
	{
    $this->localLoginModel->update($id,array("token" => NULL, "token_expiration" => NULL));
	}

	public function setValidated($id)
	{
		$now = new \Nette\DateTime();
    $this->localLoginModel->update($id,array("validated" => $now->format('Y-m-d H:i:s')));
	}

  public function query($sql)
  {
    return $this->localLoginModel->query($sql);
  }

  const TOKEN_VALIDITY = "3 day";
}

class LocalUserManager extends \App\Model\Common\TableModel
{
  public function __construct($connection)
  {
    parent::__construct($connection,"login_local");
    $this->selection->select("user.email")->as("username")->leftJoin("user")->on("user.id = login_local.user_id");
    $this->setPrimaryKey("user_id");
  }
}

class DuplicateNameException extends \Exception{}
