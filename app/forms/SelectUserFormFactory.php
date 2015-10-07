<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class SelectUserFormFactory extends Nette\Object
{
	/**
	 * @return Form
	 */
	public function create($users)
	{
		$form = new BaseForm();
  		$form->addHidden('id');
  		$form->addSelect('user_id','Uživatel',$users)->setRequired("Je nutné vybrat uživatele")->setPrompt("-- Vyberte --");
      	$form->addSubmit('send', 'Uložit');
		return $form;
	}
}
