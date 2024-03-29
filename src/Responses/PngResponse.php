<?php

namespace Cornette\Responses;

use Nette\Application\Response;
use Nette\Http\{IRequest as IHttpRequest, IResponse as IHttpResponse};

class PngResponse implements Response {
	/** @var string */
	private $source;
	
	public function __construct(string $source) {
		$this->source = $source;
	}
	
	public function getSource(): string {
		return $this->source;
	}
	
	public function send(IHttpRequest $httpRequest, IHttpResponse $httpResponse): void {
		$httpResponse->setContentType("image/png", "utf-8");
		echo $this->source;
	}
}
