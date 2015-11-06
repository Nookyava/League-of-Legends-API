<?php
	class Summoner {
		public $summoner_info = array();
		private $raw_summoner_info = array();
		
		/*
			Name: loadSummoner
			Arguments: $name (string)
			Returns: nil
			Desc: The general loading function for a summoner's info. Here we try to load it two different ways, if both fail then the summoner does not exist
		*/
		public function loadSummoner($name) {
			$clean_name = strtolower(preg_replace('/\s+/', '', $name)); // Remove all spaces from the entered name
			
			if ($this->loadSummonerFromDatabase($name)) {
				// Summoner loaded by Database
			} else if ($this->loadSummonerFromAPI($clean_name)) {
				// Start calling any additional functions needed to load the rest of the Summoner's Info
				$this->loadSummonerRankedInfo();
				
				// And store it in an Array for easy access later
				$this->summoner_info = array(
					'id' => 		$this->getSummonerID(),
					'name' => 		$this->getSummonerName(),
					'avatarid' =>	$this->getSummonerAvatarID(),
					'level' =>		$this->getSummonerLevel(),
					'tier' =>		$this->getSummonerTier(),
					'division' => 	$this->getSummonerDivision(),
					'edited' =>		date('Y-m-d'),					
				);
				
				$this->saveSummonerToDatabase();
			} else {
				// Summoner does not exist, lead them to our own 404 page
			}
		}
		
		/*
			Name: loadSummonerFromDatabase
			Arguments: $name (string)
			Returns: $found (boolean)
			Desc: Attempts to load the information from the database, if it can't find anything then it returns false
		*/
		private function loadSummonerFromDatabase($name) {
			$config = include('config/config.php');
			
			$connection = new mysqli($config['host'], $config['user'], $config['pass'], $config['database']);
			
			if ($connection->connect_error) {
				die('Connection Failed: '.$connection->connect_error);
			}
			
			$clean_name = $connection->real_escape_string($name);
			
			$sql = "SELECT * FROM summoners WHERE name = '".$clean_name."'";
			$result = $connection->query($sql);
			
			if ($result->num_rows > 0) {
				while($row = mysqli_fetch_assoc($result)) {
					$this->summoner_info = array(
						'id' => 	   	$row['id'],
						'name' =>      	$row['name'],
						'avatarid' =>	$row['avatarid'],
						'level' =>     	$row['level'],
						'tier' =>      	$row['tier'],
						'division' =>  	$row['division'],
						'edited' =>    	$row['edited'],
					);
				}
				
				return true; // As seen above obviously, we found the summoner
			} else {
				return false; // This means that the summoner we are checking for was NOT found
			}
		}
		
		/*
			Name: saveSummonerToDatabase
			Arguments: nil
			Returns: nil
			Desc: Attempts to save the information we gathered from the API to the database
		*/
		private function saveSummonerToDatabase() {
			$config = include('config/config.php');
			
			$connection = new mysqli($config['host'], $config['user'], $config['pass'], $config['database']);
			
			if ($connection->connect_error) {
				die('Connection Failed: '.$connection->connect_error);
			}
			
			$sql = "INSERT INTO summoners (id, name, avatarid, level, tier, division, edited) VALUES ('".$this->summoner_info['id']."', '".$this->summoner_info['name']."',
				'".$this->summoner_info['avatarid']."', '".$this->summoner_info['level']."', '".$this->summoner_info['tier']."', '".$this->summoner_info['division']."',
				'".$this->summoner_info['edited']."')";
			
			if ($connection->query($sql) == TRUE) {
			} else {
				echo "Error: ".$sql."<br>".$connection->error;
			}
		}
		
		/*
			Name: loadSummonerFromAPI
			Arguments: $name (string)
			Returns: $found (boolean)
			Desc: Attempts to load the information from the League of Legends API, if it can't find anything then it returns false
		*/
		private function loadSummonerFromAPI($name) {
			$config = include('config/config.php');
			$url = 'https://na.api.pvp.net/api/lol/'.$config['region'].'/v1.4/summoner/by-name/'.$name.'?api_key='.$config['key'];
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$result = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if ($httpcode == 404) {
				return false; // When 404 is returned, this means that the summoner does not exist. By this point, if it's not in our database and not in League's database then they don't exist
			}
			
			$info_decoded = json_decode($result, true);
			$this->raw_summoner_info['general'] = $info_decoded[$name];
			return true;
		}
		
		/*
			Name: loadSummonerRankedInfo
			Arguments: nil
			Returns: nil
			Desc: Pulls the ranked info for the summoner from the League of Legends API
		*/
		private function loadSummonerRankedInfo() {
			$config = include('config/config.php');
			$summoner_id = $this->getSummonerID();
			
			$url = 'https://na.api.pvp.net/api/lol/'.$config['region'].'/v2.5/league/by-summoner/'.$summoner_id.'/entry?api_key='.$config['key'];
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$result = curl_exec($ch);
			curl_close($ch);
			
			$info_decoded = json_decode($result, true);
			$this->raw_summoner_info['ranked'] = $info_decoded[$summoner_id];
		}
		
		/*
			Name: getSummonerID
			Arguments: nil
			Returns: $id (string)
			Desc: Uses data grabbed from loadsummonerFromAPI to return the summoner's ID
		*/
		private function getSummonerID() {
			return $this->raw_summoner_info['general']['id'];
		}
		
		/*
			Name: getSummonerName
			Arguments: nil
			Returns: $name (string)
			Desc: Uses data grabbed from loadsummonerFromAPI to return the summoner's name
		*/
		public function getSummonerName() {
			return $this->raw_summoner_info['general']['name'];
		}
		
		/*
			Name: getSummonerAvatarID
			Arguments: nil
			Returns: $avatar_id (string)
			Desc: Uses data grabbed from loadsummonerFromAPI to return the summoner's avatar ID
		*/
		private function getSummonerAvatarID() {
			return $this->raw_summoner_info['general']['profileIconId'];
		}
		
		/*
			name: getSummonerAvatar
			Arguments: nil
			Returns: $avatar_url (string)
			Desc: Returns the image URL from DDragon's website, uses the Avatar ID from getSummonerAvatarID.
		*/
		public function getSummonerAvatar() {
			$iconurl = 'http://ddragon.leagueoflegends.com/cdn/5.22.1/img/profileicon/'.$this->summoner_info['avatarid'].'.png';
			return $iconurl;
		}
		
		/*
			Name: getSummonerLevel
			Arguments: nil
			Returns: $level (string)
			Desc: Uses data grabbed from loadsummonerFromAPI to return the summoner's level
		*/
		private function getSummonerLevel() {
			return $this->raw_summoner_info['general']['summonerLevel'];
		}
		
		/*
			Name: getSummonerTier
			Arguments: nil
			Returns: $tier (string)
			Desc: Uses data grabbed from loadSummonerRankedInfo to return the tier that the summoner is ranked in
		*/
		
		private function getSummonerTier() {
			$path = $this->raw_summoner_info['ranked'];
			
			for ($i = 0, $size = count($path); $i < $size; $i++) {
				if ($path[$i]['queue'] == 'RANKED_SOLO_5x5') {
					return ucfirst(strtolower($path[$i]['tier']));
					break;
				}
			}
			return NULL; // This usually is because they are A) Below level 30 or B) Have never played ranked. As a result we return NULL so we can later check
		}
		
		/*
			Name: getSummonerDivision
			Arguments: nil
			Returns: $division (string)
			Desc: Uses data grabbed from loadSummonerRankedInfo to return the division that the summoner is ranked in
		*/
		private function getSummonerDivision() {
			$path = $this->raw_summoner_info['ranked'][0]['entries'];
			
			for ($i = 0, $size = count($path); $i < $size; $i++) {
				if ($path[$i]['playerOrTeamId'] == $this->getSummonerID()) {
					return $path[$i]['division'];
					break;
				}
			}
			return NULL; // Same as above in getSummonerTier.
		}
	}
?>