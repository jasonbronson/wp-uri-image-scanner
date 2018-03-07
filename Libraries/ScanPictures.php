<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class ScanPictures {
	
	/**
	 * Sort based on filename convention .001.jpg .002.jpg in that order
	 * @param array $list
	 * @return array
	 */
	public static function sortListBasedOnFilename($a, $b){
		if ($a == $b) {
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}
	
	
	/**
	 * Scans all menu items first layer of folders
	 */
	public static function menuItems($folder){
        if(empty($folder)){
            return;
        }

		$menuList = array();
		$d = new directoryScanFiles ();
		$d->ScanForFolders( $folder );
		
		return $d->folderList;
		
	}
	
	/**
	 * Scans all folders under $folder
	 */
	public static function folderList($folder){
		
		$d = new directoryScanFiles ();
		$d->ScanForFolders( $folder );
		
		return $d->folderList;
	
	}

	/**
	 * Scans all pictures under folder $folder
	 * @param $folder
	 * @param $skipRecursive = false
	 * 
	 */
	public static function pictureList($folder, $skipRecursive = true, $sortBasedOnFilename = false){
			$pictureList = array();
            $sortNumber = 0;
			$d = new directoryScanFiles();
			$d->ScanForFiles($folder, $skipRecursive);
			$d->FindAllImageTypes();
            $imgDesc="";
            $imgDesc2="";
            $shortImageName="";
			
			if(empty($d->fileList)){
				return $pictureList;
			}
			
			//echo $folder['filename'];
			//print_r($d->fileList);
			foreach($d->fileList as $fileInfo){
			  $list = array(); 
			  //if this is contact or not an image
			  if($fileInfo['filename'] == "CONTACT" || !$fileInfo['isImage']){
				continue;
			  }else{
			  	$list['filename'] = $fileInfo['filename'];
			  	//get meta data information
			  	$jpg = $_SERVER['DOCUMENT_ROOT'].'/'.$folder.'/'.$fileInfo['filename'];
			  	
			  	if($sortBasedOnFilename && strlen($fileInfo['filename']) > 8 ){
			  		//Grab the seq num in the filename .001 .002 
			  		$filename = $fileInfo['filename'];
			  		$sortNumber = substr($filename, -7, 3);
			  		if(!is_numeric($sortNumber)){
			  			$sortNumber = "";
			  		}
			  		//Grab all the filename up to .001 etc..
			  		$shortImageName = substr($filename, 0, -8);
			  		
			  	}else{
                    if($sortNumber >= 0) {
                        $sortNumber = $sortNumber + 1;
                    }

			  	}
			  	
			  	//Meta description pulled from filename
			  	$exif = @exif_read_data($jpg, null, true);
			  	$imageDescription = explode("|", $exif['IFD0']['ImageDescription']);
                if(!empty($imageDescription[0])){
                    $imgDesc=$imageDescription[0];
                    $imgDesc2=$imageDescription[1];
                }
			  	$list['cite'] = "<b>{$imgDesc}</b><br/>{$imgDesc2}<br/>Image: $shortImageName";
			  	
			  }

			  //if submenu
			  $list['submenu'] = false;
			  $pictureList[$sortNumber] = $list;
			}
			
			//Sort files based on filename convention key sequence .001 .002 etc
			if($sortBasedOnFilename){
				ksort($pictureList);
			}
			
			//print_r($pictureList); exit;
			
		return $pictureList;
	
	}
}

?>