<?php
namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;

class BaseForm extends \Nette\Application\UI\Form
{
  protected $classes = array();

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL,  $name = NULL)
 	{
  		parent::__construct($parent, $name);
  	}

  	public function render()
  	{
  	$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class=form-group';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = 'div class=col-sm-9';
		$renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
		if (!in_array("form-horizontal",$this->classes) && !in_array("form-inline",$this->classes))
		{
			$this->classes[] = "form-horizontal";
		}
		$this->getElementPrototype()->class(implode(" ",$this->classes));

		foreach ($this->getControls() as $control) {
			if ($control instanceof \Nette\Forms\Controls\Button) {
				$control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
				$usedPrimary = TRUE;
			} elseif ($control instanceof \Nette\Forms\Controls\TextBase || $control instanceof \Nette\Forms\Controls\SelectBox || $control instanceof \Nette\Forms\Controls\MultiSelectBox) {
				$control->getControlPrototype()->addClass('form-control');
			} elseif ($control instanceof \Nette\Forms\Controls\Checkbox || $control instanceof \Nette\Forms\Controls\CheckboxList || $control instanceof \Nette\Forms\Controls\RadioList) {
				$control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
			}
		}
		parent::render();
  	}

		public function addClass($class)
		{
			$this->classes[] = $class;
		}
}
