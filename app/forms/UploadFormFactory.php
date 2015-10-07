<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class UploadFileFormFactory extends Nette\Object
{
	/**
	 * @return Form
	 */
	public function create()
	{
		//$form = new BaseForm();
		$form = new BaseForm();
  	$form->addHidden('id');
  	$form->addUpload('file', 'Soubor')->setRequired("Určete soubor pro nahrání");
    $form->addSubmit('send', 'Uložit');
		return $form;
	}
}
