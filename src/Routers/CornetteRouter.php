<?php
namespace Cornette\Routers;

use Cornette\Models\RouteAddress;
use Nette\Application\Request;
use Nette\Http\{IRequest, Url, UrlScript};
use Nette\Routing\Router;

class CornetteRouter implements Router {
	private RouterService $routerService;
	
	public function __construct(RouterService $routerService) {
		$this->routerService = $routerService;
	}
	
	function constructUrl(array $params, UrlScript $refUrl): ?string {
		if(!isset($params["locale"])) {
			$params["locale"] = $this->routerService->translator->getLocale();
		}
		
		/** @var RouteAddress|null $routeAddress */
		$routeAddress = $this->routerService->routeFacade->getByParameters($params);
		if($routeAddress) {
			$slug = $routeAddress->getSlug();
			unset($params["presenter"], $params["action"], $params["locale"], $params["id"]);
			
			$url = new Url($refUrl->getBaseUrl().$slug);
			return $url->setQuery($params)->getAbsoluteUrl();
		} else {
			return null;
		}
	}
	
	function match(IRequest $httpRequest): ?array {
		$presenter = null;
		$parameters = $httpRequest->getQuery();
		
		$slug = rtrim($httpRequest->getUrl()->getPathInfo(), "/_");
		if($slug) {
			/** @var RouteAddress|null $routeAddress */
			$routeAddress = $this->routerService->routeFacade->getBySlug($slug, null, $parameters);
			if($routeAddress) {
				if($routeAddress->getParameters()) {
					$parameters += $routeAddress->getParameters();
				}
				$parameters["action"] = $routeAddress->getRoute()->getAction();
				
				$presenter = $routeAddress->getRoute()->getPresenter();
				$this->routerService->translator->setLocale($routeAddress->getLocale());
			} else {
				return null;
			}
		}
		
		$parameters += $httpRequest->getQuery();
		
		if(!$presenter) {
			return null;
		}
		
		$request = new Request($presenter, $httpRequest->getMethod(), $parameters, $httpRequest->getPost(), $httpRequest->getFiles());
		return $request->toArray();
	}
}
