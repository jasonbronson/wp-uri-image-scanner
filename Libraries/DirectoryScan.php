<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/***
 * Returns all of the files in a directory with detailed information.
* @package IOCommon
* @copyright jason
* @version 1.0
*/

abstract class DirectoryScan {

	public $fileList = array();
	protected $deDuplicatedFileList = array();
	protected $duplicateCount = 0;
	public $directoryPath = "";
	
	
	abstract public function ScanForFiles($directoryPath, $relativePath, $skipRecursive);
	abstract public function FindAllImageTypes();
	abstract public function FindMD5Checksum();
	
	
	/**
	 * Get entire list unmodified from scanForFiles
	 * @return mixed if var is set return array otherwise return null
	 */
	public function getFileList(){
		if(isset($this->fileList)){
			return $this->fileList;
		}
	
	}
	

	/**
	 * Get deduplicated list array
	 * @return mixed if var is set return array otherwise return null
	 */
	public function getDeduplicatedFileList(){
		if(isset($this->deDuplicatedFileList)){
			return $this->deDuplicatedFileList;
		}
	
	}
	
	/**
	 * Get total duplicate items found.
	 * @return int
	 */
	public function getDuplicatesFound(){
		if(isset($this->duplicateCount)){
			return $this->duplicateCount;
		}else{
			return 0;
		}
	}
	
	
}

?>