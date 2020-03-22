<?php
require_once 'vendor/autoload.php';

class Configuration
{
	function __construct()
	{
		if (php_sapi_name() != 'cli') 
		{
			echo R("This application must be run on the command line");
			exit();
		}
	}
	private function getClient()
	{
		$client = new Google_Client();
		$client->setApplicationName('Google Sheets API PHP Quickstart');
		//$client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
		$client->setScopes(Google_Service_Sheets::SPREADSHEETS);
		$client->setAuthConfig('credentials.json');
		$client->setAccessType('offline');
		$client->setPrompt('select_account consent');

		// Load previously authorized token from a file, if it exists.
		// The file token.json stores the user's access and refresh tokens, and is
		// created automatically when the authorization flow completes for the first
		// time.
		$tokenPath = 'token.json';
		if (file_exists($tokenPath)) {
			$accessToken = json_decode(file_get_contents($tokenPath), true);
			$client->setAccessToken($accessToken);
		}

		// If there is no previous token or it's expired.
		if ($client->isAccessTokenExpired()) {
			// Refresh the token if possible, else fetch a new one.
			if ($client->getRefreshToken()) {
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			} else {
				// Request authorization from the user.
				$authUrl = $client->createAuthUrl();
				printf("Open the following link in your browser:\n%s\n", $authUrl);
				print 'Enter verification code: ';
				$authCode = trim(fgets(STDIN));

				// Exchange authorization code for an access token.
				$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
				$client->setAccessToken($accessToken);

				// Check to see if there was an error.
				if (array_key_exists('error', $accessToken)) {
					throw new Exception(join(', ', $accessToken));
				}
			}
			// Save the token to a file.
			if (!file_exists(dirname($tokenPath))) {
				mkdir(dirname($tokenPath), 0700, true);
			}
			file_put_contents($tokenPath, json_encode($client->getAccessToken()));
		}
		return $client;
	}
	
	function Load($spreadsheetId,$sheetname)
	{
		$client = $this->getClient();
		$service = new Google_Service_Sheets($client);
		$range = $sheetname.'!A1:C200';
		$response = @$service->spreadsheets_values->get($spreadsheetId, $range);
		$values = $response->getValues();

		if (empty($values)) {
			print R("No config data found.\n");
		} 

		$releases = [];
		$current_release=null;
		$release_name = '';
		for($i=0;$i<count($values);$i++)
		{
			$row =  $values[$i];
			if($i==0)
			{
				$release_name = $row[1];
				echo TITLE("Building Release ".$release_name."\n");
				continue;
			}
			if($i==1)
				continue;
			foreach($row as $col=>$value)
			{
				if($col == 0)
				{
					if(trim($value)=='')
						continue;
					if(!isset($releases[$value]))
					{
						if(substr($value, 0, 1 ) === "#")
						{
							echo Y("Skipping ").$value."\n";
							continue;
						}
						
						$releases[$value]=new \StdClass();
						$releases[$value]->number=$value;
						$releases[$value]->name = "@".$release_name;
						$releases[$value]->sheetrow=$i;
					}
					$current_release = $releases[$value];
				}
				else if($col == 1)
				{
					if(trim($value)=='')
						continue;
					
					$current_release->esdm[]=$value;
					if(!file_exists('data/'.$value))
					{
						echo R('File data/'.$value." Not found\n");
						exit();
					}
				}
				
				else if($col == 2)
				{
					if(trim($value)=='')
						continue;
					$current_release->media[]=$value;
					if(!file_exists('data/'.$value))
					{
						echo R('File data/'.$value." Not found\n");
						exit();
					}
				}
				
			}
		}
		$d[] = new Google_Service_Sheets_ValueRange(
				[
					'range' => $sheetname."!"."E3",
					'values' => [[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],
					[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],[''],['']]
				]
			);
		foreach($releases as $release)
		{
			if(!file_exists('cache'))
				mkdir('cache');
			//echo 'https://release-api.wv.mentorg.com/release-api/release_detail/number/'.$release->number;
			if(!file_exists('cache/'.$release->number.".json"))
			{
				echo G("Checking ".$release->number." on release-api.wv.mentorg.com\n");
				//echo 'https://release-api.wv.mentorg.com/release-api/release_detail/number/'.$release->number;
				$data = file_get_contents('https://release-api.wv.mentorg.com/release-api/release_detail/number/'.$release->number);
				//var_dump($data);
				if(strlen($data) == 0)
				{
					echo R('Release #'.$release->number." does not exist \n");
					exit();
				}
				$data = json_decode($data);
				//dump($data->softwareFileDelivery[0]);
				//echo count($data->softwareFileDelivery);
				
				if(isset($data->softwareFileDelivery))
					if( count($data->softwareFileDelivery)>0)
					{
						$data->mediaPartNum = $data->softwareFileDelivery[0]->sdfMedias[0]->partNumber;
					}
					
				if(isset($data->medias))
					if( count($data->medias)>0)
						$data->mediaPartNum = $data->medias[0]->mediaPartNum;
				
				if(!isset($data->mediaPartNum))
				{
					 echo R($release->number." Does not have media part number assigned\n");
					 exit();
				}
				
				//dump($data);
				file_put_contents('cache/'.$release->number.".json",json_encode($data));
				//echo 'Found'."\n";
			}
			$data = file_get_contents('cache/'.$release->number.".json");
			$data = json_decode($data);
			
			$release->version = str_replace("\\","-",$data->releaseNameDetails->name);
			$release->version = str_replace("/","-",$release->version);
			
			$release->version = $release->version." [".str_replace("\\","-",$data->releaseVersion)."]";
			$release->version = str_replace("/","-",$release->version);
			$release->version = str_replace(":","-",$release->version);
			//echo "[".$release->version."]";
			
			$release->mediapn = $data->mediaPartNum;
			$release->captain = $data->createdByUser->firstName." ".$data->createdByUser->lastName;
			$release->fcsDate = $data->fcsDate;
			$release->datafolder = 'data';
			
			//var_dump($data->releaseNameDetails->name);
			
			$rowno = $release->sheetrow+1;
			$d[] = new Google_Service_Sheets_ValueRange(
				[
					'range' => $sheetname."!"."E".$rowno,
					'values' => [[$release->version]]
				]
			);
			
			//$config_sheet->setCellValue('E'.$release->sheetrow, $release->version);
			
			//$writer = new Xlsx($spreadsheet);
			//$writer->save($configfile);
		}
		
		$requestBody = new Google_Service_Sheets_BatchUpdateValuesRequest(
			[
				'valueInputOption' => 'RAW',
				'data' => $d
			]);
		$result = $service->spreadsheets_values->batchUpdate($spreadsheetId, $requestBody);
		
		
		return $releases;
	}
}