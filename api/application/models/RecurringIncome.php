<?php

class App_Model_RecurringIncome extends Standard_Model
{
	const RECUR_MONTHLY = 0;
	const RECUR_1_15 = 1;
	const RECUR_15_LAST = 2;
	const RECUR_BIWEEKLY = 3;
	const RECUR_WEEKLY = 4;

	static $recurTypes = array(
		self::RECUR_MONTHLY => 'Once a Month', 
		self::RECUR_1_15 => 'Twice Monthly on 1st and 15th', 
		self::RECUR_15_LAST => 'Twice Monthly on 15th and Last', 
		self::RECUR_BIWEEKLY => 'Every Other Week', 
		self::RECUR_WEEKLY => 'Weekly',
		);

	protected $_dbTable = "App_Model_DbTable_RecurringIncomes";

	protected $_columns = array(
		'id' => 'int',
		'user_id' => 'int',
		'name' => 'string',
		'amount' => 'float',
		'recur_type' => 'int',
		'start_date' => 'timestamp',
		'end_date' => 'timestamp',
		);

	public function occurrences($start, $end)
	{
		$dates = array();
		switch($this->recur_type)
		{
			case self::RECUR_MONTHLY:
			$d = strftime('%d', $this->start_date);
			$date = max($this->start_date, $start);
			do
			{
				$dates[] = strtotime(strftime("%m/$d/%y", $date));
				$date = strtotime('+1 month', $date);
			} while($date < $end);
			break;

			case self::RECUR_1_15:
			$date = max($this->start_date, $start);
			do
			{
				$first = strtotime(strftime("%m/1/%y", $date));
				if($first < $end)
					$dates[] = $first;

				$fifteen = strtotime(strftime("%m/15/%y", $date));
				if($fifteen < $end)
					$dates[] = $fifteen;

				$date = strtotime('+1 month', $date);
			} while($date <= $end);
			break;

			case self::RECUR_15_LAST:
			$date = max($this->start_date, $start);
			do
			{
				$fifteen = strtotime(strftime("%m/15/%y", $date));
				if($fifteen < $end)
					$dates[] = $fifteen;

				$date = strtotime('+1 month', $date);

				$last = strtotime(strftime('-1 day', $date));
				if($last < $end)
					$dates[] = $last;
			} while($date <= $end);
			break;

			case self::RECUR_BIWEEKLY:
			$date = $this->start_date;
			$dates = array();
			do {
				if($date >= $start && $date < $end)
				{
					$dates[] = $date;
				}
				$date = strtotime('+2 weeks', $date);
			} while($date < $end);
			break;

			case self::RECUR_WEEKLY:
			$date = $this->start_date;
			$dates = array();
			do {
				if($date >= $start && $date < $end)
				{
					$dates[] = $date;
				}
				$date = strtotime('+1 week', $date);
			} while($date < $end);
			break;
		}

		foreach($dates as $key => $date)
		{
			if($this->end_date > 0 && $date > $this->end_date)
			{
				unset($dates[$key]);
			}
		}

		return $dates;
	}

	public function displayRecurType()
	{
		return self::$recurTypes[$this->recur_type];
	}
}