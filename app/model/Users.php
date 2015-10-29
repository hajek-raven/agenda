<?php
namespace App\Model;

class Users extends Common\GridTableModel
{
	public function __construct(\DibiConnection $connection)
 	{
		parent::__construct($connection, "user");
		$this->getSelection()->removeClause("SELECT");
		$this->getSelection()->select("user.*, login_local.registered AS local_registered, login_imap.username AS imap_username, bakalari_code, bakalari_table")
			->leftJoin("login_local")->on("user.id = login_local.user_id")
			->leftJoin("import_bakalari")->on("user.id = import_bakalari.user_id")
			->leftJoin("login_imap")->on("user.id = login_imap.user_id");
  }

	public function asArray()
	{
		$result = array();
		$sql = "SELECT user.id, firstname, lastname FROM " . $this->getTableName() . " ORDER BY `lastname`, `firstname`";
		$data = $this->query($sql)->fetchAll();
		foreach($data as $record)
		{
			$result[$record->id] = $record->lastname . ", " .$record->firstname;
		}
		return $result;
	}

	public function fromGroupAsArray($group, array $add = array())
	{
		$result = array();
		$sql = "SELECT user.id, firstname, lastname FROM " . $this->getTableName() . " LEFT JOIN membership ON membership.user_id = user.id WHERE membership.group_id = " . $group;
		if ($add)
		{
			$list = implode(", ",$add);
			$sql .= " OR user.id IN(".$list.")";
		}
		$sql .= " ORDER BY `lastname`, `firstname`";
		$data = $this->query($sql)->fetchAll();
		foreach($data as $record)
		{
			$result[$record->id] = $record->lastname . ", " .$record->firstname;
		}
		return $result;
	}

	public function fromGroupRoleAsArray($groupName, $add = array())
	{
		$groupData = $this->query("SELECT id FROM `group` WHERE role_name = \"" . $groupName ."\"")->fetch();
		if($groupData)
		{
			return($this->fromGroupAsArray($groupData->id,$add));
		}
		else
		{
			return array();
		}
	}
	
  	public function activeUsersAsArray($append = array())
  	{
  		$sql = 'SELECT id,lastname,firstname,email FROM user WHERE active = 1 ';
  		if($append) $sql .= ' OR id IN('.implode(',',$append).')';
  		$sql .= ' ORDER BY lastname,firstname';
  		$users = $this->query($sql)->fetchAll();
        $usersArray = array();
        foreach($users as $user)
        {
            $usersArray[$user->id] = $user->lastname . ', ' . $user->firstname;
        }
        return $usersArray;
  	}
	  
  	public function listedUsersAsArray($list = array())
  	{
  		$sql = 'SELECT id,lastname,firstname,email FROM user WHERE id IN('.implode(',',$list).')';
  		$sql .= ' ORDER BY lastname,firstname';
  		$users = $this->query($sql)->fetchAll();
        $usersArray = array();
        foreach($users as $user)
        {
            $usersArray[$user->id] = $user->lastname . ', ' . $user->firstname;
        }
        return $usersArray;
  	}  
}
