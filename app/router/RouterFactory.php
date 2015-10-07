<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		//Route::$defaultFlags = Route::SECURED;
		$router = new RouteList();
		$router[] = new Route('sign/<action>[/<id>]', array("module" => "Service", 'presenter' => 'Sign','action' => 'default'));
		$router[] = new Route('dashboard/<presenter>/<action>[/<id>]', array("module" => "Dashboard", 'presenter' => 'Homepage','action' => 'default'));
		$router[] = new Route('works/<presenter>/<action>[/<id>]', array("module" => "Works", 'presenter' => 'Homepage','action' => 'default'));
		$router[] = new Route('<presenter>/<action>[/<id>]', array("module" => "Front", 'presenter' => 'Homepage','action' => 'default'));
		return $router;
	}

}
