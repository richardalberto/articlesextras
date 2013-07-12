<?php
class Image{
	var $name;
	var $description;
	var $fileId;
	
	function Image($name, $description, $fileId){
		$this->name = $name;
		$this->description = $description;
		$this->fileId = $fileId;
	}
	
	function getName(){
		return $this->name;
	}
	
	function getDescription(){
		return $this->description;
	}
	
	function getFileId(){
		return $this->fileId;
	}
}
?>