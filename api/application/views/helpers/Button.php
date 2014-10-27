<?php
class App_View_Helper_Button extends Zend_View_Helper_Abstract
{
	public $view;

	public function setView(Zend_View_Interface $view)
	{
		$this->view = $view;
	}
	
	public function button(array $button)
	{
		$classes = array();
		$text = '';
		if(isset($button['text']))
		{
			$text = $this->view->escape($button['text']);
		}
		
		$id = '';
		if(isset($button['id']))
			$id = " id=\"{$button['id']}\"";

		if(isset($button['class']))
			$classes[] = $button['class'];
		
		$data = '';
		if(isset($button['url']))
		{
			if(is_array($button['url']))
			{
				$url = $this->view->url($button['url'], null, true);
			}
			else
			{
				$url = $button['url'];
			}
			$data = " data-url=\"{$url}\"";
		}
		
		if(isset($button['ajax']))
		{
			$classes[] = 'ajax';
		}
		else
		{
			$classes[] = 'direct';
		}
		
		$output = '<button'
			. $id
			. ' class="'. implode(' ', $classes) . '"'
			. $data
			. '>'
			. $text
			. '</button>';
		
		return $output;
	}
}