<?php

namespace Cornette\Routers;

use Contributte\Translation\Translator;
use Cornette\Models\RouteFacade;

class RouterService {
	public RouteFacade $routeFacade;
	
	public Translator $translator;
	
	public function __construct(RouteFacade $routeFacade, Translator $translator) {
		$this->routeFacade = $routeFacade;
		$this->translator = $translator;
	}
}
