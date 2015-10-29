<?php
namespace App\Model\Import;

class FileUserImport extends BaseUserImport
{
  
  const INTERNAL = 0;
  const BAKALARI_ZACI = 1;
  const BAKALARI_UCITELE = 2;
  const UNKNOWN = -1;
  
  static private $separator = ";";
  
  public function __construct(\App\Model\Users $userModel, \App\Model\Groups $groupModel)
  {
	  parent::__construct($userModel, $groupModel);
  }
  
  public function exportAll($filename)
  {
	  $data = $this->userModel->fetchAll();
    $fp = fopen($filename, 'w');
    foreach ($data as $index => $record) 
    {
      if($index == 0) {
        $arr = (array)$record;
        $keys = array_keys($arr);
        fputcsv($fp, $keys, self::$separator);
      }
      fputcsv($fp, (array)$record, self::$separator);
    }
    fclose($fp);
  }
  
  public function detectType(array $columnNames)
  {
    
  }
  
  public function import($filename)
  {
     $fp = fopen($filename, 'r');
     if($fp)
     {
       $firstLine = fgets($fp);
       if ($firstLine)
       {
         $columnNames = explode(self::$separator,$firstLine);
         switch ($this->detectType($columnNames))
         {
           case $this->INTERNAL : ;
           default : throw new DataImportException("Neznámé názvy sloupců.");
         }
       }
       else
       {
         throw new DataImportException("Soubor je prázdný.");
       }
       fclose($fp);
     }
     else
     {
       throw new DataImportException("Nelze otevřít soubor.");
     }
  }
}

class DataImportException extends \Exception {}