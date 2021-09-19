<?php
namespace Cornette\Components;

use Contributte\Translation\Translator;
use Cornette\Models\RouteFacade;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;

class SlugControl extends Control {
	/** @var RouteFacade */
	private $routeFacade;
	
	/** @var Translator */
	private $translator;
	
	public function __construct(RouteFacade $routeFacade, Translator $translator) {
		$this->routeFacade = $routeFacade;
		$this->translator = $translator;
	}
	
	public function render(string $slug): void {
		/** @var Presenter|null $presenter */
		$presenter = $this->getPresenter();
		if(isset($presenter)) {
			$this->routeFacade->insertSlug($presenter->getName(), $presenter->getAction(), $presenter->getParameters(), $slug, $this->translator->getLocale());
		}
	}
}
