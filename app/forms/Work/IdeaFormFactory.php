<?php

namespace App\Forms\Work;

use Nette,
	Nette\Application\UI\Form;


class IdeaFormFactory extends Nette\Object
{
	protected $model;

	/**
	 * @return Form
	 */
	public function create($model)
	{
		$form = new \App\Forms\BaseForm();
		$this->model = $model;
  	$form->addHidden('id');
  	$form->addText('name', 'Název')->setRequired("Vyplňte název")->setAttribute('placeholder', 'Rychlost sprintu průměrného lenochoda');
    $form->addTextArea('description', 'Téma')->setRequired("Vyplňte téma práce")->setAttribute('placeholder', 'Práce se zabývá analýzou rychlosti průměrného lenechoda. Její součástí bude příprava tréninkového plánu směřujícího k účasti zmíněného obratlovce na následujících olympijských hrách ve sprintu.');
    $form->addTextArea('resources', 'Prostředky')->setAttribute('placeholder', 'několik pokusných lenochodů, stopky, metla jako motivační prostředek');
    $form->addText('subject', 'Zkratka předmětu, kam práce spadá')->setRequired("Vyplňte zkratku vyučovacího předmětu")->setAttribute('placeholder', 'TEV');
    $form->addText('coworkers', 'Počet řešitelů')->setRequired("Vyplňte maximální počet souběžných řešitelů tohoto zadání")->addRule(Form::INTEGER, 'Počet řešitelů by měl být celé číslo.');;
    $form->addCheckbox('active', 'Aktivní');
		$form->addSubmit('send', 'Uložit');
		return $form;
	}
}
