<?php
class MEDIA
{
	public static function Generate($release)
	{
		$files = [];
		if(!isset($release->media))
		{
			echo Y("No MEDIA Files\n");
			return -1;
		}
		foreach($release->media as $file)
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
			$files[] = $file;
		}
		
		$media_folder = $release->name."/".$release->number."-".$release->version."/".$release->number."_media";
		delete_directory($media_folder);
		mkdir($media_folder);
		$zip = new ZipArchive;
		
		$zipfilename = $media_folder."/".$release->mediapn.".zip";
		$retval = $zip->open($zipfilename , ZipArchive::CREATE);
		
		if ($retval=== TRUE)
		{
			foreach($files as $file)
			{
				$zip->addFile($release->datafolder.'/'.$file,$file);
			}
			$zip->close();
		}
		else
		{
			echo R("zip error ".$retval);
			exit();
		}
		$md5 = md5_file ($zipfilename );
		$checklist = $md5." ".$release->mediapn.".zip";
		file_put_contents($media_folder."/Media_checksums.txt",$checklist);
		echo G("Media generated\n");
		return 0;
	}
}
