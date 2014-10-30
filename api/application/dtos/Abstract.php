<?php

abstract class App_Dto_Abstract implements \JsonSerializable {
	
	public function jsonSerialize() {
		$vars = get_object_vars($this);
		return $vars;
	}
}