<?php

namespace Ixolit\CDE;

use Ixolit\CDE\Controller\ControllerLogic;

class CDEInit {
	public static function execute() {

		$controllerLogic = new ControllerLogic(
			CDE::getRequestAPI(),
			CDE::getResponseAPI(),
			CDE::getFilesystemAPI()
		);

		$controllerLogic->execute();
	}
}
