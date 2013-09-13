<?php
/* php 5.3
 *
 * multipartCSV
 *
 * This Class is used to solve a issue, when you export your data into csv, and open it through excel.
 * if rows are more than 65k then excel will only display 65k rows.
 * So we are breaking our csv in parts. each file will contain 60k rows.
 * and make a zip containg these files. that will be downloadble laso.
 *
 * @author chandra kishor
 * @version 1.0
 * @copyright Copyright chandrakishor 2013
 * 
 *
 */
?>

<?php

class MultipartCSV {
    /*
     * @var  static $nol
     * default value is 60000 lines per csv
     */
    private static $nol = '60000';
    
    /*
     * @var $path
     * store path info , means wher to store zip file.
     */
    private static $path = '../zip/';
    
     /*
     * @var $filename
     * store path info , means wher to store zip file.
     */
    private static $filename = 'sample';
     /*
     * @var $zipname
     * store path info , means wher to store zip file.
     */
    private static $zipname = 'sample';
    
    
    /*
     * @var array $header
     * header for each csv
     */
    private static $header = array();
    
    /*
     * @var array $footer
     * footer for each csv
     */
    private static $footer = array();
    
    /*
     * @access private
     * counter
     */
    
    private static $count = 0;
    
    /*
     * @access private
     * file name with path
     */
    private static $fullFileName = '';
    
    /*
     * @access private
     * filepointer
     */
    private static $fp = '';
     /*
     * @access private
     * filearray
     */
    private static $fileArray = array();
    /*
     * @access private
     * csv file extension
     */
    private static $ext = ".csv";
    
    /*
     * @access private
     * data array
     */
    private static $data = array();
    /*
     * @access public
     * set properties at once.
     */
    public  function __construct($filename, $nol, $header, $footer, $path){
        if ($filename != '') {
          //remove extension from filename
          $extArr = explode('.',$filename);
          if(array_pop($extArr)){
            self::$filename = implode('.', $extArr);
            self::$zipname = self::$filename;
            
          }

        } 
        
        if ($nol >= '1' && is_numeric($nol)) {
            self::$nol = (int)($nol);
        }
        
        if (is_array($header) && !empty($header)) {
            self::$header = $header;
        }
        
        if (is_array($footer) && !empty($footer)) {
            self::$footer = $footer;
        }
               
        if ($path != '') {
            if (is_dir($path)) {
                self::$path = $path;
            } else {
                $this->_throwError('Supplied Path does not exist!');
            }
        }
        
        if (is_dir(self::$path)){
            
           if (is_dir(self::$path.self::$zipname)) {
                        $counter = 1;
                        $folder = self::$zipname;
                       while(is_dir(self::$path.$folder)){
                            $folder = self::$zipname;                            
                            $folder = $folder."_".$counter;
                            $counter++;
                       }
                        
                self::$zipname = $folder;
                }
                
                if (!mkdir(self::$path.self::$zipname)) {
                        $this->_throwError('can not create folder!');
                }  
                
           } else {
            $this->_throwError('path not exist!');
            return false;
        }
    
    }
    
    /*
     * @access private
     * @method throwError
     * @param string $msg
     * @return null
     */
    
    private function _throwError($msg){
        echo  "<br/><hr/>";
        print_r($msg);
        echo  "<br/><hr/>";
        if  (self::$path.self::$zipname != '')  { 
            if(is_dir(self::$path.self::$zipname)){
               
                     foreach(glob(self::$path.self::$zipname . '/*') as $file) { 
                     unlink($file); 
                } 

              
            }
        }
        exit;
    }
    
    /*
     * @access private
     * @method createCsv
     * @return boolean;
     */
    
    private function _createCSV(){
        if (self::$filename == '' || self::$zipname == '' || self::$path == '') {
            $this->_throwError('Error in createCSV();');
            return false;
        }
        
        $fileArr = explode("_", self::$filename);        
        if (is_numeric(array_pop($fileArr))){
            self::$filename = implode("_", $fileArr);
        }
        if (file_exists(self::$path.self::$zipname."/".self::$filename.self::$ext)){
             $counter = 1;
                       $filename = self::$filename;
                        
                       while(file_exists(self::$path.self::$zipname."/".$filename.self::$ext)){
                           
                           $filename = self::$filename;                            
                            $filename = $filename."_".$counter;
                            $counter++;
                       }
                        
                self::$filename = $filename;
        }
                 self::$fullFileName = self::$path.self::$zipname."/".self::$filename.self::$ext;
                $fp = fopen(self::$fullFileName, 'w') or die($this->_throwError('Error Occured When Creating CSV.')) ;
                self::$fileArray[] = self::$filename;
                self::$fp = $fp;
			
        
    }
    
    
    /*
     * @access public
     * 
     * method to send data in bulk at once and it create on csv
     */
    public function dataAtOnceInOneCSV($data, $zip=true) {
        if (!is_array($data) || empty($data)) {
            $this->_throwError("Data is not in array");            
        }
        
       
       
        $this->_createCSV();
        $this->_addHeader();
        foreach($data as $key=>$value){
                if(!is_array($value)){
                    $value = array(serialize($value));
                }
                
            fputcsv(self::$fp, $value)or die($this->_throwError('can nto add data in csv!'));
            unset($data[$key]);
        }        
        $this->_addFooter();
            
         
        fclose(self::$fp) or die("Not working");    
        if ($zip) {
           if(!$this->makeZip())
               {
                $this->_throwError('can not create a zip');
               }
        }   
        
    }
    
    
    /*
     * @access public
     * 
     * method to send data in bulk at once and it will auto crete in parts
     */
    public function dataAtOnceInParts($data,$zip=true) {
        if (!is_array($data) || empty($data)) {
            $this->_throwError("Data is not in array");            
        }
        
       
        $flag = true;
        do{
            
             $this->_createCSV();
            $flag=  $this->_addDataInCsv($data);
         
            fclose(self::$fp) or die("Not working");
          
        }while(!$flag);
        if ($zip) {
           if(!$this->makeZip())
               {
                $this->_throwError('can not create a zip');
               }
        }   
        
        
    }
     
      /*
     * @access public
     * 
     * method to send data per csv, it will not check $nol  
     */
    public function dataPerCSV($data) {
        
        if (!is_array($data) || empty($data)) {
            $this->_throwError("Data is not in array");          
        }
       
                      
             $this->_createCSV();
             $this->_addHeader();
            foreach($data as $key=>$value){
                if(!is_array($value)){
                    $value = array(serialize($value));
                }
                
            fputcsv(self::$fp, $value)or die($this->_throwError('can nto add data in csv!'));
            unset($data[$key]);
        }        
        $this->_addFooter();
        fclose(self::$fp) or die("Not working");
          
       
       
       
        
        
    }
     
     /*
     * @access public
     * 
     * method to insert data one by one
     */
    public function oneByOne($data) {
        if (!is_array($data) || empty($data)) {
            $this->_throwError("Data is not in array");            
        }
         self::$data = array_merge(self::$data, $data);
          $flag = true;
        do{
            
            $flag=  $this->_writeOneByOne($data);
            
        }while(!$flag);
         
    }
    
    /*
      * @access private
      * add data in csv as per nol it is used for parts
      * @param filepointer
      * 
      */
     
     private function _writeOneByOne(){
       
        if(self::$count%self::$nol == 0){
            $counter = 0;
            $this->_createCSV();
            $this->_addHeader();
        }else{
            $counter = self::$count%self::$nol;
            self::$fp= fopen(self::$fullFileName, 'a+') or die($this->_throwError('Error Occured When Creating CSV.')) ;
        }
        
        
       
        $flag = true;
        $real = 0;
        foreach(self::$data as $key=>$value){
            if(!is_array($value)){
                $value = array(serialize($value));
            }
            if ($counter >= self::$nol){
            $flag = false;
            break;
            }
            fputcsv(self::$fp, $value)or die($this->_throwError('can nto add data in csv!'));
            unset(self::$data[$key]);
            $counter++;
            $real++;
        }
        self::$count = self::$count+$real;      
          
           
        
           if ($flag) {
            fclose(self::$fp) or die("Not working");
            return true;
        } else {
             $this->_addFooter();
            fclose(self::$fp) or die("Not working");
            return false;
        }
       
				
			
               
     }
    
    
     
     /*
      * @access private
      * add data in csv as per nol it is used for parts
      * @param filepointer
      * 
      */
     
     private function _addDataInCsv(&$data){
        $this->_addHeader();
        $counter = 0;
        $flag = true;
        foreach($data as $key=>$value){
            if(!is_array($value)){
                $value = array(serialize($value));
            }
            if ($counter >= self::$nol){
            $flag = false;
            break;
            }
            fputcsv(self::$fp, $value)or die($this->_throwError('can nto add data in csv!'));
            unset($data[$key]);
            $counter++;
        }
        self::$count = self::$count+$counter;
        $this->_addFooter();
        
        if ($flag) {
            return true;
        } else {
            return false;
        }
				
			
               
     }
    
    /* 
     * @access private
     * method to add header in csv
     *
     */
    
    private function _addHeader(){
        if(!empty(self::$header))
                    fputcsv(self::$fp, self::$header) or die($this->_throwError('Can not add header'));			
		
    }
    
    /*
     * @access private
     * method to add footer iin csv
     */
    private function _addFooter(){
         if(!empty(self::$footer))
        fputcsv(self::$fp, self::$footer) or die($this->_throwError('Can not add header'));			
    }
    
    /**
     * @access private
     * make a zip 
     *
     */
    
    public function makeZip(){
     
		$zip = new ZipArchive();
		if($zip->open(self::$path.self::$zipname.".zip",ZIPARCHIVE::CREATE) !== true) {
			return false;
		}
		//add the files
                
                
		foreach(self::$fileArray as $file) {
                    
			$zip->addFile(self::$path.self::$zipname."/".$file.self::$ext, $file.self::$ext) or die($this->_throwError('can not mak a zip'));
		}
		
		$zip->close();
		return true;
		
	
    }
}

?>