<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var \App\Model\Files @inject */
	public $filesModel;

	public $ajax = 'on';

	protected $title;

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function __construct(Nette\DI\Container $context = NULL)
	{
		parent::__construct($context);
	}

	protected function beforeRender()
	{
		$this->template->title = $this->title;
	}

	public function afterRender()
	{
	    if ($this->isAjax() && $this->hasFlashSession())
	        $this->invalidateControl('flashes');
	}

	protected function downloadFile($id)
	{
		$record = $this->filesModel->get($id);
		if($record)
		{
			$fileName = $this->filesModel->getStoragePath() . $record->id/* . "." . $record->extension*/;
			if (file_exists($fileName))
			{
				$response = new \Nette\Application\Responses\FileResponse($fileName, $record->original, $record->mime);
   			$this->sendResponse($response);
				return true;
			}
		}
		return false;
	}
}
