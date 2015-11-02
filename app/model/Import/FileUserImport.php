<?php
namespace App\Model\Import;

class FileUserImport extends BaseUserImport
{
  
  const INTERNAL = 0;
  const BAKALARI_ZACI = 1;
  const BAKALARI_UCITELE = 2;
  const UNKNOWN = -1;
  
  static private $separator = ";";
  public $bom;
  
  public function __construct(\App\Model\Users $userModel, \App\Model\Membership $membershipModel)
  {
	  parent::__construct($userModel, $membershipModel);
    $this->bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) );
  }
  
  public function exportAll($filename)
  {
	  $data = $this->userModel->fetchAll();
    $fp = fopen($filename, 'w');
    fputs($fp, $this->bom);
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
  
  private function containsAllColumnNames($expected, $real)
  {
    foreach ($expected as $col)
    { 
      if (!in_array($col,$real)) return false;
    }
    return true;
  }
  
  public function detectType(array $columnNames)
  {
    if ($this->containsAllColumnNames(array("firstname",	"lastname",	"gender",	"birthdate",	"active",	"email"),$columnNames)) return self::INTERNAL;
    elseif ($this->containsAllColumnNames(array("RODNE_C",	"JMENO",	"PRIJMENI",	"POHLAVI",	"DATUM_NAR",	"E_MAIL", "LOGIN", "INTERN_KOD", "FUNKCE"),$columnNames)) return self::BAKALARI_UCITELE;
    else return self::UNKNOWN;
  }
  
  public function import($filename,$settings)
  {
     $fp = fopen($filename, 'r');
     if($fp)
     {
       $firstLine = fgets($fp);
       if ($firstLine)
       {
         if(substr( $firstLine, 0, 3 ) === $this->bom)  {$firstLine = substr($firstLine, 3);}
         $columnNames = explode(self::$separator,trim($firstLine));
         switch ($this->detectType($columnNames))
         {
           case self::INTERNAL : 
              while ($line = fgets($fp))
              {
                if ($settings->encoding != "UTF-8")
                  $line = iconv($settings->encoding, "UTF-8", $line);
                $row = explode($settings->separator,trim($line));
                if (count($row) == count($columnNames))
                {
                  $data = array();
                  foreach($columnNames as $index => $column) $data[$column] = $row[$index]; 
                  $this->importRecord($data);                  
                }
              }
              break;
           case self::BAKALARI_UCITELE : 
              while ($line = fgets($fp))
              {
                if ($settings->encoding != "UTF-8")
                  $line = iconv($settings->encoding, "UTF-8", $line); 
                $row = explode($settings->separator,trim($line));
                if (count($row) == count($columnNames))
                {
                  $data = array();
                  $columnNames = $this->prepareColumnsBakalariUcitele($columnNames);
                  foreach($columnNames as $index => $column) $data[$column] = $row[$index];
                  $data = $this->prepareDataBakalariUcitele($data);
                  $this->importRecord($data);                 
                }                
              }
              break;
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