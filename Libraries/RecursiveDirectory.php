<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Class to provide different methods of getting files recursively.
 * @author jason
 *
 */
class RecursiveDirectory {

	private $filename = "";
	private $fileList = array();
	private $error = "";
	
	/**
	 * Get all files inside a directory recursively.
	 * @param $directory (String)
	 * @param $fullPathIncluded (Boolean)
	 * @param $directoryPathToInclude (String)
	 * 
	 * @return fileList (Array)
	 */
	public function getFilesDirectory($directory, $fullPathIncluded = true, $directoryPathToInclude = null, $recursive = true){
		
		$this->logMessage("Directory is {$directory}", "debug");
		
		//Directory must exist inside of the images webroot folder and not be null.
  		if(!is_dir($directory) || empty($directory)){
  			$message = "Directory {$directory} doesn't exist or is null.";
  			$this->logMessage("Directory {$directory} doesn't exist or is null.", "err");
  			$this->error = "Directory {$directory} doesn't exist.";
  			return;
  		}
		
  		if($recursive){
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory),
			RecursiveIteratorIterator::CHILD_FIRST);
  		}else{
  			$iterator = new IteratorIterator(new DirectoryIterator($directory));
  		}
  		
		
		foreach ($iterator as $path) {
			
			if ($path->isFile() && !$path->isDot() && substr($path->getFilename(), 0, 1) != "." ) {
				//File is readable 
				if($path->isReadable()){
					$this->filename = $path->getFilename();	
					
					if($fullPathIncluded){
						//Retain only absolute filename path
						$fullPath = $path->getPath();
						$this->fileList[] = $fullPath."/".$this->filename;
						$this->logMessage("Absolute filename path $fullPath", "debug");
					}elseif(!empty($directoryPathToInclude)){
						//Use directory path included
						$this->fileList[] = $directoryPathToInclude."/".$this->filename;
						$this->logMessage("Included filename path $directoryPathToInclude", "debug");
					}else{
						//Retain only filename
						$this->fileList[] = $this->filename;
						$this->logMessage("Filename Only {$this->filename}.", "debug");
					}
					
					
				}else{
					$message = "File {$this->filename} is not readable.";
					$this->logMessage($message, "err");
					$this->error = $message;
				}
			}
		}
		
		return $this->fileList;
		
	}
		
	
	 /**
	  * Gets the filename if it exists.
	  * @return Filename (String)
	  */
	 public function getFilename(){
	 	return $this->filename;
	 }	

	 /**
	  * Gets any errors that occur in the class.
	  * @return Error (String)
	  */
	 public function getError(){
	 	return $this->error;
	 }

	 /**
	  * Logs messages to standard Symfony logging class.
	  * @param $log
	  * @param $level="debug"
	  * @return none
	  */
	 private function logMessage($log, $level="debug"){
	 	error_log("Class ".get_class($this).": ".$log);
	 }

}