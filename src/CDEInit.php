<?php

namespace Ixolit\CDE;

use Ixolit\CDE\Context\Page;
use Ixolit\CDE\Controller\ControllerLogic;

class CDEInit {
	public static function execute() {

		$controllerLogic = new ControllerLogic(
			Page::get()->getRequestAPI(),
			Page::get()->getResponseAPI(),
			Page::get()->getFilesystemAPI()
		);

		$controllerLogic->execute();
	}
}
