<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class FileFormFactory extends Nette\Object
{
	/**
	 * @return Form
	 */
	public function create()
	{
		//$form = new BaseForm();
		$form = new BaseForm();
  	$form->addHidden('id');
  	$form->addText('original', 'Název souboru')->setRequired("Určete název souboru");
	$form->addText('mime', 'MIME')->setRequired("Určete identifikátor MIME");
	$form->addText('extension', 'Koncovka souboru')->setRequired("Určete koncovku");
	$form->addCheckbox('public', 'Veřejný');
    $form->addSubmit('send', 'Uložit');
		return $form;
	}
}
