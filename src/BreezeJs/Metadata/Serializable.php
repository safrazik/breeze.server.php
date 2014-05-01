<?php

namespace BreezeJs\Metadata;

class Serializable implements \Serializable {

	private $_props = array();
	
	public function __construct($props = array()) {
		$defaults = $this->defaults();
		foreach($defaults as $key => $val){
			$this->_props[$key] = $val;
		}
		foreach($props as $key => $val){
			$this->_props[$key] = $val;
		}
	}
	
	public function __get($name) {
		return isset($this->_props[$name]) ? $this->_props[$name] : null;
	}
	
	public function __set($name, $value) {
		$this->_props[$name] = $value;
	}
	
	protected function defaults(){
		return array();
	}
	
	public function serialize() {
		return serialize($this->_props);
	}

	public function unserialize($data) {
		$props = unserialize($data);
		foreach($props as $name => $value){
			$this->_props[$name] = $value;
		}
	}
	
	public function toArray(){
		$props = array();
		foreach($this->_props as $name => $value){
			if(is_array($value)){
				$valueProcessed = array();
				foreach($value as $key => $val){
					if(is_object($val)){
						$valueProcessed[$key] = $val->toArray();						
					}
					else {
						$valueProcessed[$key] = $val;
					}
				}
				$props[$name] = $valueProcessed;
			}
			else if(is_object($value) && $value instanceof Serializable){
				$props[$name] = $value->toArray();
			}
			else {
				$props[$name] = $value;
			}
		}
		return $props;
	}
	
	public function toJson(){
		return json_encode($this->toArray());
	}
	
}