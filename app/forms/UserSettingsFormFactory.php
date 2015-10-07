<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class UserSettingsFormFactory extends Nette\Object
{
	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new BaseForm();
  		$form->addHidden('user_id');
  		$rows = array(10,20,30,50,100);
  		$form->addSelect('table_rows','Počet řádků v tabulkách')->setItems($rows,false)->setRequired("Zvolte, kolik řádků se má zobrazovat v tabulkách.")->setPrompt("-- Vyberte --");
      	$form->addSubmit('send', 'Uložit');
		return $form;
	}
}
