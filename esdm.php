<?php
class ESDM
{
	public static function Generate($release)
	{
		if(!file_exists($release->name))
			mkdir($release->name);
	
		$version_folder = $release->name."/".$release->number." ".$release->version;
		
		if(!file_exists($version_folder))
			mkdir($version_folder);
	
		$esdm_folder = $version_folder."/".$release->number."_esdm";
		if(file_exists($esdm_folder))
		{
			echo G("Already Generated . Skipping\n");
			return 1;
		}
		//delete_directory($esdm_folder);
		//mkdir($esdm_folder);
		$checksumlist = '';
		$del = '';
		if(!isset($release->esdm))
		{
			echo Y("No ESDM Files\n");
			return -1;
		}
		
		foreach($release->esdm as $file)
		{
			$md5_file = $release->datafolder.'/'.$file.'.md5';
			$md5 = md5_file ($release->datafolder.'/'.$file);
			if(file_exists($md5_file))
			{
				$cmd5 = file_get_contents($release->datafolder.'/'.$md5_file);
				if($md5 != $cmd5)
				{
					echo R($file." Computed MD5 [".$md5."] does not match with [".$cmd5."]");
					exit();
				}
			}
			if(!file_exists($esdm_folder))
				mkdir($esdm_folder);
			copy($release->datafolder.'/'.$file, $esdm_folder."/".$file);
			$checksumlist .= $del.$md5." ".$file;
			$del="\r\n";
		}
		file_put_contents($esdm_folder."/ESDM_checksums.txt",$checksumlist);
		echo G("Esdm generated\n");
		return 0;
	}
}