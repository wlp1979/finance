<?php

class OFX
{
	protected $_xml;
	
	public static function fromFile($filename)
	{
		if(file_exists($filename))
		{
			$data = file_get_contents($filename);
			return new self($data);
		}
		else
			throw new OFXException('file not found');
	}
	
	public function __construct($data)
	{
		list($header, $ofxdata) = explode("<OFX>", $data);
		$ofxdata = '<OFX>' . $ofxdata;
		
		$lines = preg_split('/[\r\n]+/', $header);
		foreach($lines as $line)
		{
			if(preg_match('/(\w)+\:(\w)+/', $line, $matches))
			{
				//header line
				if($matches[1] == 'OFXHEADER' && $matches[2] != '100')
				{
					throw new OFXException('incompatible OFX header version');
				}
				elseif($matches[1] == 'DATA' && $matches[2] != 'OFXSGML')
				{
					throw new OFXException('incompatible OFX content type');
				}
			}
		}

		$content = "<?xml version='1.0'?>" . $this->_closeAllTags($ofxdata);
		libxml_use_internal_errors(true);
		$this->_xml = simplexml_load_string($content);
		if($this->_xml === false)
		{
			throw new OFXException("An error occured while parsing the OXL data.  Use libxml_get_errors to see the error messages");
		}
	}
	
	protected function _closeAllTags($original)
	{
		$cursor = 0;
		$prevName = '';
		$corrected = '';
		$remaining = $original;
		while($cursor < strlen($original) && preg_match('/\<([\w\.\/]+)\>/', $remaining, $matches))
		{
			$tag = $matches[0];
			$name = $matches[1];
			$tagPos = strpos($remaining, $tag);
			if(!empty($prevName))
			{
				$prevContent = substr($remaining, 0, $tagPos);
				$corrected .= trim($prevContent) . "</$prevName>";
			}
			
			if(strpos($name, '/') !== FALSE || strpos($remaining, "</$name>") !== FALSE)
			{
				$prevName = '';
			}
			else
			{
				$prevName = $name;
			}
			
			$corrected .= $tag;
			$cursor += strlen($tag) + $tagPos;
			$remaining = substr($original, $cursor);
		}
		
		return $corrected;
	}
	
	protected function _xmlToTransaction($xml)
	{
		if(is_array($xml))
		{
			$transactions = array();
			foreach($xml as $element)
			{
				$transactions[] = $this->_xmlToTransaction($element);
			}
			return $transactions;
		}
		elseif($xml instanceof SimpleXMLElement)
		{
			$transaction = new OFXTransaction();
			if(isset($xml->FITID))
			{
				$transaction->id = (string) $xml->FITID;
			}
			
			if(isset($xml->TRNTYPE))
			{
				$transaction->type = (string) $xml->TRNTYPE;
			}
			
			if(isset($xml->TRNAMT))
			{
				$transaction->amount = (string) $xml->TRNAMT;
			}
			
			if(isset($xml->CHECKNUM))
			{
				$transaction->check = (string) $xml->CHECKNUM;
			}
			
			if(isset($xml->NAME))
			{
				$transaction->name = (string) $xml->NAME;
			}
			
			if(isset($xml->MEMO))
			{
				$transaction->memo = (string) $xml->MEMO;
			}
			
			if(isset($xml->DTPOSTED))
			{
				$transaction->date = $this->_parseDate((string) $xml->DTPOSTED);
			}
			
			return $transaction;
		}
	}
	
	protected function _parseDate($dateString)
	{
		if(preg_match('/(\d{4})(\d{2})(\d{2})(\d{2})?(\d{2})?(\d{2})?\.?\d*\[?([\+\-])?([0-9]*)?.*/', $dateString, $matches))
		{
			$year = $matches[1];
			$month = $matches[2];
			$day = $matches[3];
			$hour = ($matches[4] === '') ? 00 : $matches[4];
			$minutes = ($matches[5] === '') ? 00 : $matches[5];
			$seconds = ($matches[6] === '') ? 00 : $matches[6];
			$correction = '+00:00';
			if($matches[8] !== '')
			{
				$correction = (empty($matches[7])) ? '+' : $matches[7];
				$correction .= str_pad($matches[8], 2, '0', STR_PAD_LEFT);
				$correction .= ":00";
			}
			$timeString = "{$year}-{$month}-{$day}T{$hour}:{$minutes}:{$seconds}.00{$correction}";
			return strtotime($timeString);
		}
	}
	
	public function transactions()
	{
		return $this->_xmlToTransaction($this->_xml->xpath('//*/STMTTRN'));
	}
	
	public function credits()
	{
		return $this->_xmlToTransaction($this->_xml->xpath("//*/STMTTRN[TRNTYPE='CREDIT']"));
	}
	
	public function debits()
	{
		return $this->_xmlToTransaction($this->_xml->xpath("//*/STMTTRN[TRNTYPE='DEBIT']"));
	}
	
	public function checks()
	{
		return $this->_xmlToTransaction($this->_xml->xpath("//*/STMTTRN[TRNTYPE='CHECK']"));
	}
	
	public function bankId()
	{
		$nodes = $this->_xml->xpath('//*/FID | //*/BANKID');
		if(count($nodes) > 0)
			return (string) $nodes[0];
		else
			return null;
	}
	
	public function account()
	{
		$nodes = $this->_xml->xpath('//*/ACCTID');
		if(count($nodes) > 0)
			return (string) $nodes[0];
		else
			return null;
	}
}

class OFXTransaction
{
	public $date;
	public $amount;
	public $id;
	public $name;
	public $memo;
	public $check;
	public $type;
}

class OFXException extends Exception {}