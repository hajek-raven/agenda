<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class ImportFileFormFactory extends Nette\Object
{
	/**
	 * @return Form
	 */ 
	 
	public function create(array $encoding)
	{
		$form = new BaseForm();
  		$form->addHidden('id');
  		$form->addUpload('file', 'Soubor')->setRequired("Určete soubor pro nahrání");
		$form->addSelect('encoding','Kódování souboru')->setItems($encoding, FALSE)->setDefaultValue($encoding[0]);;
		$form->addText('separator','Oddělovač sloupců')->setDefaultValue(";");
    	$form->addSubmit('send', 'Uložit');
		return $form;
	}
}
