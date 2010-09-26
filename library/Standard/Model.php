<?php

abstract class Standard_Model
{
	protected $_data;

	protected static $_tables = array();

	protected $_buffering = false;
	protected $_buffer = array();
	
	public function __construct(array $data = null)
	{
		if(!empty($data))
		{
			foreach($data as $column => $value)
			{
				$this->setColumn($column, $value);
			}
		}
	}

	public function __set($name, $value)
	{
		return $this->setColumn($name, $value);
	}

	public function __get($name)
	{
		return $this->getColumn($name);
	}

	public function __sleep()
	{
		return array('_data');
	}

	public function toArray()
	{
		$data = array();
		foreach($this->_columns as $name => $value)
		{
			$data[$name] = $this->getColumn($name);
		}

		return $data;
	}

	public function setColumn($name, $value)
	{
		$method = 'set' . Standard_Inflector::toCamelCase($name);
		if (method_exists($this, $method))
		{
			$this->$method($value);
		}
		elseif(isset($this->_columns[$name]))
		{
			$type = $this->_columns[$name];
			$method = 'filter' . ucfirst($type);
			if(method_exists($this, $method))
				$value = $this->$method($value);
				
			$this->_data[$name] = $value;
		}
		else
		{
			throw new Exception("Invalid attribute $name specified");
		}

		return $this;
	}

	public function getColumn($name)
	{
		$method = 'get' . Standard_Inflector::toCamelCase($name);
		if (method_exists($this, $method))
		{
			return $this->$method();
		}
		elseif(isset($this->_data[$name]))
		{
			return $this->_data[$name];
		}

		return null;
	}
	
	public function getDbTable()
	{
		if(empty($this->_dbTable))
			throw new Exception('There is no dbtable for this class');

		$class = $this->_dbTable;
		if(!isset(self::$_tables[$class]))
		{
			$table = new $class();
			if(!($table instanceof Standard_Db_Table))
				throw new Exception('Invalid table class');

			self::$_tables[$class] = $table;
		}

		return self::$_tables[$class];
	}

	public function loadFromDb($row)
	{
		if($row instanceof Zend_Db_Table_Row)
		{
			$data = $row->toArray();
		}
		elseif($row instanceof stdClass)
		{
			$data = (array) $row;
		}
		elseif(is_array($row))
		{
			$data = $row;
		}

		foreach($data as $column => $value)
		{
			if(isset($this->_columns))
				$this->_data[$column] = $value;
		}

		return $this;
	}

	public function filterString($value)
	{
		return trim($value);
	}
	
	public function filterTimestamp($value)
	{
		if(empty($value) || is_numeric($value))
		{
			return $value;
		}
		elseif(is_string($value))
		{
			$time = strtotime($value);
			if($time === false)
				throw new Exception("Value could not be translated into a timestamp: $value");
			return $time;
		}
		else
		{
			throw new Exception('Value could not be translated into a timestamp: ' . print_r($value));
		}
	}
	
    public function displayDate($column = 'date', $format = '%m/%d/%y')
    {
		if($this->$column == 0 || $this->$column == '')  
			return '';

        return strftime($format, (int) $this->$column);
    }
    
    public function displayDateTime($column = 'date', $format = '%m/%d/%y %l:%M %p')
    {
		if($this->$column == 0 || $this->$column == '')  
			return '';

        return strftime($format, (int) $this->$column);
    }
    
    public function displayTime($column = 'date', $format = '%l:%M %p')
    {
		if($this->$column == 0 || $this->$column == '')  
			return '';

        return strftime($format, (int) $this->$column);
    }

	public function displayDaysFromNow($column = 'date')
	{
		if($this->$column == 0 || $this->$column == '')  
			return '';

		$day = 60 * 60 * 24;
		$date = $this->$column;
		$now = time();
		$tomorrow = strtotime('tomorrow');
		$delta = $date - $now;
		if($delta < $day && $date < $tomorrow)
		{
			return 'today';
		}
		elseif($delta <= $day && $date > $tomorrow)
		{
			return 'tomorrow';
		}
		else
		{
			$days = floor($delta/$day);
			$daysWord = ($days > 1) ? 'days' : 'day';
			return "$days $daysWord from now";
		}
	}

    public function displayCurrency($column = 'amount')
    {
		$currency = new Zend_Currency(array('value' => $this->$column));
        return (string) $currency;
    }
    
	public function save()
	{
		$table = $this->getDbTable();
		$data = $this->_data;
		
		if($this->_buffering)
		{
			$this->_buffer[] = $data;
			return $this;
		}
		
		if(isset($data['id']) && $data['id'] > 0)
		{
			$where = array('id = ?' => $data['id']);
			unset($data['id']);
			$table->update($data, $where);
		}
		else
		{
			$id = $table->insert($data);
			$this->_data['id'] = $id;
		}
		
		return $this;
	}
	
	public function find($id)
	{
		$table = $this->getDbTable();
		$rows = $table->find($id);
		if(count($rows) < 1)
		{
			return false;
		}
		
		$row = $rows->current();
		return $this->loadFromDb($row);
	}
	
	public function fetchByUser(App_Model_User $user)
	{
		$table = $this->getDbTable();
		$rows = $table->fetchByUserId($user->id);
		$items = array();
		$class = get_class($this);
		foreach($rows as $row)
		{
			$item = new $class();
			$item->loadFromDb($row);
			$items[$item->id] = $item;
		}
		
		return $items;
	}
	
	public function findMany(array $ids)
	{
		if(empty($ids)) return array();
		
		$table = $this->getDbTable();
		$rows = $table->findMany($ids);
		$items = array();
		$class = get_class($this);
		foreach($rows as $row)
		{
			$item = new $class();
			$item->loadFromDb($row);
			$items[$item->id] = $item;
		}
		
		return $items;
	}

	public function buffer()
	{
		$this->_buffering = true;
	}

	public function flush()
	{
		$table = $this->getDbTable();
		$table->bulkInsert($this->_buffer);
		$this->_buffer = array();
	}
}
