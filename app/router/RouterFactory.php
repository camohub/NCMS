<?php

namespace App;


use	Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter,
	Nette\Utils\Strings;


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
		$router = new RouteList();

		// zachytí adresy ktoré obsahujú modul a param id = \d+
		// a vymaže akcie show napr. u článkou lebo produktov
		$router[] = new Route('<module blog|eshop|forum>/<presenter>[/<action>]/<id \d+>[/<title>]',
			array('presenter' => 'default',
				'action' => 'show',
				'title' => array(
					Route::FILTER_IN => function($s)
					{
						return $s;
					},
					ROUTE::FILTER_OUT => function($s)
					{
						return Strings::webalize($s, NULL, FALSE);
					}
				)
			)

		);

		// zachytí adresy, ktoré neobsahujú param id z predošlej routy
		$router[] = new Route('<module blog|eshop|forum>[/<presenter>[/<action>]]',
			array('presenter' => 'default',
				'action' => 'default'
			)

		);

		/* Pre admin sekciu zbytočná, preto zakomentovaná ...
		// špeciálna routa pre submoduly, v ktorej musia byť presenter a action povinné,
		// lebo inak Nette nevie rozoznať čo je module a čo presenter
		$router[] = new Route('<module .+>/<presenter>/<action>',
			array('module' => array(
				Route::FILTER_IN => function($s)
				{
					$s = strtolower($s);
					/*$s = preg_replace('#([.-])(?=[a-z])#', '$1 ', $s); *//*
					$s = ucwords($s);
					/*$s = str_replace('. ', ':', $s);
                    $s = str_replace('- ', '', $s);
                    return $s; *//*
					return strtr($s, '/', ':');
				},
				ROUTE::FILTER_OUT => function($s)
				{
					$s = strtr($s, ':', '/');
					/*$s = preg_replace('#([^.])(?=[A-Z])#', '$1-', $s); *//*
					$s = strtolower($s);
					$s = rawurlencode($s);
					$s = strtr($s, array('%2F' => '/'));
					return $s;
					/*strtr($s, '.', '/'); *//*
				}
			)
			)
		);*/

		$router[] = new Route('<presenter>[/<action>[/<id>]]',
			array('presenter' => 'default',
				'action' => 'default'
			)
		);

		return $router;
	}

}
