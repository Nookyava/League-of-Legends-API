<?php
	class LoLAPI {
		private $summonerinfo = array();
		private $summonername;
		private $summonerid;
		
		/* 
			Name: setSummonerInfo
			Arguments: name (string)
			Returns: nil
			Desc: Sets all the variables necessary
		*/
		public function setSummonerInfo($enteredname) {
			$this->summonerinfo['general'] = $this->requestSummonerInfo($enteredname);
			$this->summonerinfo['stats'] = $this->getSummonerStats($this->summonerinfo['general'][$enteredname]['id']);
			
			$this->summonername = $enteredname;
			$this->summonerid = $this->summonerinfo['general'][$enteredname]['id'];
		}
		
		/*
			Name: requestSummonerInfo
			Arguments: name (string)
			Returns: result (array)
			Desc: Gets data about the summoner like their level, avatar and name
		*/
		private function requestSummonerInfo($name) {
			$config = include('config/config.php'); // Include the config
			$url = 'https://na.api.pvp.net/api/lol/'.$config['region'].'/v1.4/summoner/by-name/'.$name.'?api_key='.$config['key'];
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //this will return the content as a string instead of printing it out instantly $result = curl_exec($ch);
			$result = curl_exec($ch);
			curl_close($ch);
			
			$summonerdata = json_decode($result, true); //This will put the json data in an associate array for quick access later.
			return $summonerdata;
		}
		
		/* 
			Name: getSummonerStats
			Arguments: id (string),
			Returns: stats (array)
			Desc: Returns how many times the player has won on that map
		*/
		private function getSummonerStats($id) {
			$config = include('config/config.php'); // Include the config
			$url = 'https://na.api.pvp.net/api/lol/'.$config['region'].'/v1.3/stats/by-summoner/'.$id.'/summary/?api_key='.$config['key'];
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$result = curl_exec($ch);
			curl_close($ch);
			
			$summonerdata = json_decode($result, true); //This will put the json data in an associate array for quick access later.
			return $summonerdata;
		}
		
		/*
			Name: getSummonerName
			Arguments: name (string), summonerdata (array)
			Returns: level (string)
			Desc: Returns the level of the summoner
		*/
		public function getSummonerName() {
			return $this->summonerinfo['general'][$this->summonername]['name'];
		}
		
		/*
			Name: getSummonerLevel
			Arguments: name (string), summonerdata (array)
			Returns: level (string)
			Desc: Returns the level of the summoner
		*/
		public function getSummonerLevel() {
			return $this->summonerinfo['general'][$this->summonername]['summonerLevel'];
		}
		
		/*
			Name: getSummonerAvatarPNG
			Arguments: name (string), summonerdata(array)
			Returns: avatarid (string)
			Desc: Returns the avatar of the player
		*/
		public function getSummonerAvatar() {
			$iconver = '5.2.2';
			$iconurl = 'http://ddragon.leagueoflegends.com/cdn/'.$iconver.'/img/profileicon/'.$this->summonerinfo['general'][$this->summonername]['profileIconId'].'.png';
			
			return $iconurl;
		}
		
		/* 
			Name: getSummonerWins
			Arguments: summonerstats (array)
			Returns: wins (string)
			Desc: Returns how many times the player has won on that map
		*/
		public function getSummonerWins() {
			for($i = 0, $size = count($this->summonerinfo['stats']['playerStatSummaries']); $i < $size; $i++) {
				if ($this->summonerinfo['stats']['playerStatSummaries'][$i]['playerStatSummaryType'] == 'Unranked') {
					return $this->summonerinfo['stats']['playerStatSummaries'][$i]['wins'];
					break;
				}
			}
			return 0;
		}
	}
?>