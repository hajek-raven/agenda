<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class SelectLineFormFactory extends Nette\Object
{
	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new BaseForm();
  		$form->addHidden('id');
  		$form->addSelect('selection','Výběr')->setPrompt("Vyberte");
    	$form->addSubmit('send', 'Uložit');
		return $form;
	}
}