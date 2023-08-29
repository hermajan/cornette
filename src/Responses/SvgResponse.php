<?php

namespace Cornette\Responses;

use Nette\Application\Response;
use Nette\Application\UI\Template;
use Nette\Http\{IRequest as IHttpRequest, IResponse as IHttpResponse};

class SvgResponse implements Response {
	/** @var Template|string */
	private $source;
	
	/**
	 * @param Template|string $source
	 */
	public function __construct($source) {
		$this->source = $source;
	}
	
	/**
	 * @return Template|string
	 */
	public function getSource() {
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
