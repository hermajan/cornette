<?php

namespace Cornette\Routers;

use Contributte\Translation\Translator;
use Cornette\Models\RouteFacade;

class RouterService {
	/** @var RouteFacade */
	public $routeFacade;
	
	/** @var Translator */
	public $translator;
	
	public function __construct(RouteFacade $routeFacade, Translator $translator) {
		$this->routeFacade = $routeFacade;
		$this->translator = $translator;
	}
}
