<?php
namespace App\Model\Import;

class BakalariImport extends \Nette\Object
{
	protected $interfaceLocation;
	protected $key;
	
	public function __construct(\App\Model\Users $userModel, \App\Model\Membership $membershipModel)
	{
		
	}
	
	public function setInterfaceLocation($url)
	{
		$this->interfaceLocation = $url;
	}
	
	public function getInterfaceLocation()
	{
		return $this->interfaceLocation;
	}
	
	public function setKey($key)
	{
		$this->key = $key;
	}
	
	private function sendCommand($path,$parameters = array())
	{
		if ($this->key) $parameters["key"] = $this->key;
		$incoming = file_get_contents($this->interfaceLocation . $path . "?" . http_build_query($parameters));
		try
		{
			return (\Nette\Utils\Json::decode($incoming));
		}
		catch (Exception $ex)
		{
			return false;
		}
	}
	
	public function refreshSource()
	{
		return $this->sendCommand("import/");
	}	
	
	public function refreshSubjects()
	{
		return $this->sendCommand("import/subjects/");
	}
	
	public function refreshStudents()
	{
		return $this->sendCommand("import/students/");
	}
	
	public function refreshTeachers()
	{
		return $this->sendCommand("import/teachers/");
	}
	
	public function refreshClasses()
	{
		return $this->sendCommand("import/classes/");
	}
	
	public function refreshGroups()
	{
		return $this->sendCommand("import/groups/");
	}
	
	public function refreshLoads()
	{
		return $this->sendCommand("import/loads/");
	}	
	
	public function importSubjects()
	{
		return $this->sendCommand("subjects/dump/");
	}
	
	public function importTeachers()
	{
		return $this->sendCommand("teachers/dump/");
	}
	
	public function importClasses()
	{
		return $this->sendCommand("classes/dump/");
	}
	
	public function importStudents()
	{
		return $this->sendCommand("students/dump/");
	}
	
	public function importGroups()
	{
		return $this->sendCommand("groups/dump/");
	}
	
	public function importMembership()
	{
		return $this->sendCommand("membership/dump/");
	}
	
	public function importLoads()
	{
		return $this->sendCommand("loads/dump/");
	}	
}