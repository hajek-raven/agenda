<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class NewLineFormFactory extends Nette\Object
{
	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new BaseForm();
  	$form->addHidden('id');
  	$form->addText('description','Text');
    $form->addSubmit('send', 'Uložit');
		return $form;
	}
}
