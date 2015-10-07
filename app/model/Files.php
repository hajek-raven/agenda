<?php
namespace App\Model;

class Files extends \App\Model\Common\GridTableModel
{
	protected $fileStorage;

	public function __construct(\DibiConnection $connection)
 	{
		parent::__construct($connection, "file");
    $this->getSelection()->removeClause("SELECT");
    $this->getSelection()->select("file.*, user.firstname, user.lastname")->leftJoin("user")->on("file.user_id = user.id");
    $this->setPrimaryKey("file.id");
  }

	public function setStoragePath($storagePath)
	{
		$this->fileStorage = $storagePath;
	}

	public function getStoragePath()
	{
		return $this->fileStorage;
	}

	public function storeUploaded($file, $user_id, $settings = array())
 	{
		if ($file->isOk)
		{
			$mime = $file->getContentType();
			$name = $file->getName();
			$safe = $file->getSanitizedName();
			$size = $file->getSize();
			$extension = \Nette\Utils\Strings::lower(pathinfo($name, PATHINFO_EXTENSION));
			$id = $this->insert(array("original" => $name, "user_id" => $user_id,	"extension" => $extension, "mime" => $mime, "public" => 0, "size" => $size));
		  $file->move($this->fileStorage . $id/* . "." . $extension*/);
		}
		else
		{
			throw new Exception("There is something wrong with uploaded file.");
		}
	}

	public function reserveStorage($user_id, $filename)
	{
		$extension = \Nette\Utils\Strings::lower(pathinfo($filename, PATHINFO_EXTENSION));
		$id = $this->insert(array("original" => $filename, "user_id" => $user_id,	"extension" => $extension, "mime" => "", "public" => 0, "size" => 0));
		return \Nette\Utils\ArrayHash::from(array("id" => $id, "filename" => $this->fileStorage . $id /*.".". $extension*/));
	}

	public function refreshData($id)
	{
		$record = $this->query("SELECT * FROM file WHERE id = ". $id)->fetch();
		if($record && $this->exists($id))
		{
			$filename = $this->getStoragePath() . $record->id/* . "." . $record->extension*/;
			$size = filesize($filename);
			$finfo = new \finfo(FILEINFO_MIME);
			$mime = $finfo->file($filename);
			$this->update($id,array("mime" => $mime,"size" => $size));
		}
	}

	public function exists($id)
	{
		return file_exists($this->getStoragePath() . $id/* . "." . $record->extension*/);
	}

	protected function minimime($fname)
	{
    $fh=fopen($fname,'rb');
    if ($fh) {
        $bytes6=fread($fh,6);
        fclose($fh);
        if ($bytes6===false) return false;
        if (substr($bytes6,0,3)=="\xff\xd8\xff") return 'image/jpeg';
        if ($bytes6=="\x89PNG\x0d\x0a") return 'image/png';
        if ($bytes6=="GIF87a" || $bytes6=="GIF89a") return 'image/gif';
        return 'application/octet-stream';
    }
    return false;
}

	public function delete($id)
	{
		$record = $this->query("SELECT * FROM file WHERE id = ". $id)->fetch();
		if($record)
		{
			if ($this->exists($id))
			try
			{
			unlink($this->fileStorage . $record->id/* . "." . $record->extension*/);
			}
			catch (Exception $ex)
			{
				throw new Exception("Nepodařilo se fyzicky smazat soubor " . $record->id);
			}
			parent::delete($id);
		}
		else
		{
			return false;
		}
	}

	public function canAccess($id,$user)
	{
		$record = $this->get($id);
		if ($record->public) return true;
		if ($record->user_id == $user) return true;
		return false;
	}
}
