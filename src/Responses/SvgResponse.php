<?php

namespace Cornette\Responses;

use Nette\Application\Response;
use Nette\Application\UI\Template;
use Nette\Http\{IRequest as IHttpRequest, IResponse as IHttpResponse};

class SvgResponse implements Response {
	/** @var string */
	private $source;
	
	public function __construct(string $source) {
		$this->source = $source;
	}
	
	public function getSource(): string {
		return $this->source;
	}
	
	public function send(IHttpRequest $httpRequest, IHttpResponse $httpResponse): void {
		$httpResponse->setContentType("image/svg+xml", "utf-8");
		
		if($this->source instanceof Template) {
			$this->source->render();
		} else {
			echo $this->source;
		}
	}
}
