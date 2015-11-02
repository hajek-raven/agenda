<?php
namespace App\Model\Import;

abstract class BaseUserImport extends \Nette\Object
{
  protected $userModel;
  protected $membershipModel;
  protected $bakalariColumnTranscription = array(
    "RODNE_C" => "personal_identification_number",	
    "JMENO" => "firstname",	
    "PRIJMENI" => "lastname",
    "POHLAVI" => "gender",	
    "DATUM_NAR" => "birthdate",	
    "E_MAIL" => "email", 
    "LOGIN" => "imap_username", 
    "INTERN_KOD" => "bakalari_code",
    "TEL_MOBIL" => "phone");
  
  public $encoding = array("UTF-8", "Windows-1250", "ISO-8859-2", "CP1250", "CP852", "ISO-8859-1");  

  public function __construct(\App\Model\Users $userModel, \App\Model\Membership $membershipModel)
  {
    $this->userModel = clone $userModel;
    $this->membershipModel = clone $membershipModel;
  }
  
  public function query($sql)
  {
	  return $this->userModel->query($sql);
  }
  
  public function importRecord($data)
  {
    if (empty($data["id"]))
    {
      if(!empty($data["bakalari_code"]))
      {
        $rec = $this->query("SELECT id FROM user WHERE bakalari_code = \"{$data["bakalari_code"]}\"")->fetch();
        if ($rec) $id = $rec["id"]; else $id = null;
      }
      else
      {
        $id = null;
      }
    }
    else
    {
      $id = $data["id"];
      unset($data["id"]);      
    }
    $userData = array();
    $bakalariData = array();
    $imapLoginData = array();
    $localLoginData = array();
    $userData["firstname"] = $data["firstname"];
    $userData["lastname"] = $data["lastname"];
    if (empty($data["title"])) $userData["title"] = null; else $userData["title"] = $data["title"];
    if (empty($data["title_after"])) $userData["title_after"] = null; else $userData["title_after"] = $data["title_after"];
    if (empty($data["personal_identification_number"])) $userData["personal_identification_number"] = null; else $userData["personal_identification_number"] = $data["personal_identification_number"];
    $userData["gender"] = $data["gender"];
    $userData["birthdate"] = strtr($data["birthdate"],array("\n" => "", "\"" => ""));
    if (empty($data["active"])) $userData["active"] = 1; else $userData["active"] = $data["active"];
    if (empty($data["enabled"])) $userData["enabled"] = 1; else $userData["enabled"] = $data["enabled"];
    $userData["email"] = $data["email"];
    if (empty($data["phone"])) $userData["phone"] = null; else $userData["phone"] = $data["phone"];	
    if (empty($data["bakalari_code"])) $userData["bakalari_code"] = null; else $userData["bakalari_code"] = $data["bakalari_code"];  
    
    if (is_numeric($id))
    {
      try
      {
        $this->userModel->update($id,$userData);
      }
      catch (Exception $ex)
      {
        // do nothing at this moment
      }      
    }
    else
    {
      try
      {
        $id = $this->userModel->insert($userData);
      }
      catch (Exception $ex)
      {
        // do nothing at this moment
      }
    }
    $this->query("DELETE FROM login_imap WHERE user_id = $id");
    if (!empty($data["imap_username"]))
    {
      $this->query("INSERT INTO login_imap (`user_id`,`username`) VALUES ($id,\"{$data["imap_username"]}\")");
    }
    if (!empty($data["groups"]))
    {
      foreach($data["groups"] as $groupName)
      {
        $this->membershipModel->inNamedGroup($id,$groupName);
      }
    }  
  }
  
  public function prepareColumnsBakalariUcitele($data)
  {
    for($i = 0; $i < count($data); $i++)
    {
      if(isset($this->bakalariColumnTranscription[$data[$i]])) {$data[$i] = $this->bakalariColumnTranscription[$data[$i]];}
    }
    return $data;
  }
  
  public function prepareDataBakalariUcitele($data)
  {
    if ($data["gender"] == "Z") $data["gender"] = "F"; else $data["gender"] = "M";
    $data["personal_identification_number"] = strtr($data["personal_identification_number"],array("/" => ""));
    $data["birthdate"] = date("Y-m-d", strtotime($data["birthdate"]));
    $data["groups"][] = "teacher";
    return $data;
  }
}