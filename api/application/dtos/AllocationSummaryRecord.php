<?php

class App_Dto_AllocationSummaryRecord extends App_Dto_Abstract {

	public $name;

	public $remainingAllocated;

	public $currentlyAvailable;

	public function __construct($name, $remainingAllocated, $currentlyAvailable) {
		$this->name = $name;
		$this->remainingAllocated = $remainingAllocated;
		$this->currentlyAvailable = $currentlyAvailable;
	}
}