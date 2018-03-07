<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/***
 * Returns all of the files in a directory with detailed information.
 * @package IOCommon
 * @copyright jason
 * @version 1.0
 */
class DirectoryScanFiles extends DirectoryScan {
	
	public $logFileHandle;
	
	public function __construct(){
		
		$this->logFileHandle = fopen("/tmp/wordpress-uri-image-scanner.log", "a");
	}
	
	public function __destruct(){
		fclose($this->logFileHandle);
	}
	
	public function log($message){
	
		fwrite($this->logFileHandle, $message."\n");
	
	}
	
	
	/**
	 * This will scan a directory for all subfolders
	 * @param unknown_type $directoryPath
	 */
	public function ScanForFolders($directoryPath){
        $folderList="";
		try{
			$this->checkPath($directoryPath);
		}catch(exception $exception){
			throw new exception($exception);
		}

		$iterator = new \DirectoryIterator($directoryPath);
		foreach ($iterator as $folder) {

		  if($folder->isDir() && !$folder->isDot()){
				$folderInfo = array();
				$folderName = $folder->getFilename();

				//If user does not specify a custom name file then we set the name as the folder name
				$folderInfo['customname'] = $folderName;
				$folderInfo['filename'] = $folderName;
				$folderList[] = $folderInfo;
			}

		}
		var_dump($folderList);
		//check for a ordering file to sort the folder names
		if(file_exists($directoryPath."/sort")){
			$sortList = file($directoryPath."/sort", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$folderList = $this->sortArrayByArray($folderList ,$sortList);
		}

		$this->folderList = $folderList;

	}


	/**
	 * This will scan a directory for all the files and store
	 * filename, path, mtime, size, extension for any files found.
	 * @param string $directoryPath path of where to scan for files.
	 */
	public function ScanForFiles($directoryPath, $relativePath, $skipRecursive = true){

		try{
			$this->checkPath($directoryPath);
		}catch(exception $exception){
			throw new exception($exception);
		}

		if($skipRecursive){
			$iterator = new \IteratorIterator(new \DirectoryIterator($directoryPath));
		}else{
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directoryPath),
                                              RecursiveIteratorIterator::CHILD_FIRST);
		}

		foreach ($iterator as $filename) {

			if ($filename->isFile()) {

				$fileInfo['filename'] = $filename->getFilename();
				$fileInfo['mtime'] = $filename->getMTime();
				$fileInfo['path'] = $filename->getPath();
				$fileInfo['size'] = $filename->getSize();
				$fileInfo['extension'] = pathinfo($filename->getFilename(), PATHINFO_EXTENSION); //$filename->getExtension();
				$fileInfo['relativepath'] = $relativePath; //$this->getRelativePath($filename->getPath(), $relativePath);
				//Store all the files information into multi dimension array
				$fileList[] = $fileInfo;
			}

		}

		return $fileList;

	}

	private function getRelativePath($path, $relativePathString){

		if(!empty($path) && !empty($relativePathString)){
			$found = strpos($path, $relativePathString);
			if($found !== false){
				$chars = strlen($relativePathString);
                return substr($path, $found + $chars);
			}

		}

	}


	/**
	 * Finds all possible files which are images and
	 * sets isImage=true in fileList.
	 *
	 */
	public function FindAllImageTypes(){

		if( !isset($this->fileList)){
			return;
		}

		//image type list
		$imageType = array("jpg", "gif", "jpeg", "tiff", "bmp", "png", "tif");

		foreach($this->fileList as $fileInfo){

			$extension = strtolower($fileInfo['extension']);
			if(in_array($extension, $imageType)){
				$fileInfo['isImage'] = true;

			}else{
				$fileInfo['isImage'] = false;

			}
			$fileList[] = $fileInfo;

		}
		$this->fileList = $fileList;


	}

	/**
	 * Performs an MD5 file checksum on all files under FileList.
	 * This operation should be performed just after a full directory scan is done.
	 */
	public function FindMD5Checksum(){

		if( !isset($this->fileList)){
			return;
		}

		foreach($this->fileList as $fileInfo){

			$fileInfo['md5'] = md5_file($fileInfo['path']."/".$fileInfo['filename']);
			$fileList[] = $fileInfo;

		}
		$this->fileList = $fileList;


	}

	/**
	 * Checks a directory is valid.
	 * @param string $directoryPath
	 * @throws Exception
	 */
	private function checkPath($directoryPath){
		if(empty($directoryPath)){
			throw new Exception("Directory path is required.");
		}
		/*if( ! is_dir($directoryPath) || ! file_exists($directoryPath)){
			throw new Exception("Directory path must be a directory and exist.");
		}
		if( ! is_dir($directoryPath) || ! file_exists($directoryPath)){
			throw new Exception("Directory path must be a directory and exist.");
		}*/
		
	}
	
	/**
	 * Removes duplicate items from an Array which has a key of "md5" string
	 * Uses $this->fileList
	 * @param none;
	 * @return none;
	 */
	function RemoveDuplicateMD5String(){
	
		if(!is_array($this->fileList)){
			return;
		}
	
		$deDuplicatedFileList = array();
		$duplicateValueFound = false;
	
		foreach ($this->fileList as $fileInfo){
				
			$match = null;
			foreach($deDuplicatedFileList as $value){
				if($value['md5'] == $fileInfo['md5']){
					//Duplicate value found
					$duplicateValueFound = true;
					$this->log("DUPLICATE {$value['md5']} \n");
				}
			}
				
			if( !$duplicateValueFound){
				$deDuplicatedFileList[] = $fileInfo;
			}else{
				//Count all duplicates
				$this->duplicateCount++;
			}
		}
		
		//$this->duplicateCount = $duplicateCount;
		//unset($duplicateCount);
	
		$this->deDuplicatedFileList = $deDuplicatedFileList;
		unset($deDuplicatedFileList);
	}
	
	/**
	 * Sorts an array by using another ordered array's values
	 * @param array $array
	 * @param array $orderArray
	 * @return array
	 */
	private function sortArrayByArray($array,$orderArray) {
		if(empty($array) || empty($orderArray)){
			return $array;
		}
		
		$ordered = array();
		
		foreach($orderArray as $orderValue) {
			
			$count = 0;
			foreach($array as $arrayValue){
			 if( trim($orderValue) == trim($array[$count]['filename']) ) {
			 		$ordered[$count] = $array[$count];
			 }	
			 $count++;
			}
			
			
		}
		
		return $ordered + $array;
	}
	
}

?>
