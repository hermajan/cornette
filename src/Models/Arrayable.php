<?php

namespace Cornette\Models;

trait Arrayable {
	public function fromArray(array $data): self {
		foreach($data as $key => $value) {
			if(property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
		
		return $this;
	}
	
	public function toArray(): array {
		return get_object_vars($this);
	}
	
	public function toFormArray(): array {
		$properties = $this->toArray();
		foreach($properties as $key => $value) {
			if(is_a($this->{$key}, \DateTime::class)) {
				$properties[$key] = $this->{$key}->format("Y-m-d");
			}
		}
		
		return $properties;
	}
}
