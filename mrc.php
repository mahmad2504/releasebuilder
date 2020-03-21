<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MRC
{
	function __construct()
	{
		$this->mrc_spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("mrc/mrc_template.xlsm"); 
		$this->mrc_sheet = $this->mrc_spreadsheet->getActiveSheet();
		$mrc_data = $this->mrc_spreadsheet->getActiveSheet()->toArray(null,true,true,true); 
		$this->CELL_RELEASE_NUMBER='';
		$this->CELL_RELEASE_VERSION='';
		$this->CELL_RELEASE_CAPTAIN='';
		$this->CELL_RELEASE_DATE='';
		for($i=1;$i<=count($mrc_data);$i++)
		{
			$row =  $mrc_data[$i];	
			foreach($row as $col=>$value)
			{
				if($value == '%RELEASE_NUMBER%')
					$this->CELL_RELEASE_NUMBER=$col.$i;
				else if($value == '%RELEASE_VERSION%')
					$this->CELL_RELEASE_VERSION=$col.$i;
				else if($value == '%RELEASE_CAPTAIN%')
					$this->CELL_RELEASE_CAPTAIN=$col.$i;
				else if($value == '%RELEASE_DATE%')
					$this->CELL_RELEASE_DATE=$col.$i;
					
			}
		}
	}
	public function Generate($release)
	{
		$all_mrc_files = [];
		$folder = $release->name."/".$release->number." ".$release->version."/".$release->number."_mrc";
		delete_directory($folder);
		mkdir($folder);
		$this->mrc_sheet->setCellValue($this->CELL_RELEASE_NUMBER, $release->number);
		$this->mrc_sheet->setCellValue($this->CELL_RELEASE_VERSION, $release->version);
		$this->mrc_sheet->setCellValue($this->CELL_RELEASE_CAPTAIN, $release->captain);
		$this->mrc_sheet->setCellValue($this->CELL_RELEASE_DATE, $release->fcsDate);
		
		
		$writer = new Xlsx($this->mrc_spreadsheet);
		$writer->save($folder.'/mrc_'.$release->number.'.xlsm');
		$all_mrc_files[] = $folder.'/mrc_'.$release->number.'.xlsm';
		$files = glob("mrc/*.*");
		//var_dump($files);
		
		foreach($files as $file)
		{
			if($file == 'mrc/mrc_template.xlsm')
				continue;
			
		  $file_to_go = str_replace_once('mrc',$folder,$file);
		  //echo $file."\n";
		  //echo $file_to_go."\n";
		  $all_mrc_files[] = $file_to_go;
		  copy($file, $file_to_go);
		}
		
		//dump($all_mrc_files);
		$zip = new ZipArchive;
		$zipfilename = $release->name."/".$release->number." ".$release->version."/".$release->number."_mrc.zip";
		@unlink($zipfilename);
		$retval = $zip->open($zipfilename , ZipArchive::CREATE);
		
		if ($retval=== TRUE)
		{
			foreach($all_mrc_files as $file)
			{
				$zip->addFile($file,basename($file));
			}
			$zip->close();
		}
		delete_directory($folder);
		
		echo G("MRC generated\n");
	}
}