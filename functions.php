<?php	
	/*
		Name: requestSummonerInfo
		Arguments: name (string)
		Returns: result (array)
		Desc: Gets data about the summoner like their level, avatar and name
	*/
	function requestSummonerInfo($name) {
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
	function getSummonerStats($id) {
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
		Name: getSummonerWins
		Arguments: summonerstats (array)
		Returns: wins (string)
		Desc: Returns how many times the player has won on that map
	*/
	function getSummonerWins($summonerstats) {
		for($i = 0, $size = count($summonerstats['playerStatSummaries']); $i < $size; $i++) {
			if ($summonerstats['playerStatSummaries'][$i]['playerStatSummaryType'] == 'Unranked') {
				return $summonerstats['playerStatSummaries'][$i]['wins'];
				break; // I break here just so I make sure it doesn't return 0. Seems redundant but I'd rather make sure.
			}
		}
		return 0;
	}
	
	/*
		Name: getSummonerID
		Arguments: name (string), summonerdata (array)
		Returns: id (string)
		Desc: Returns the id of the summoner
	*/
	function getSummonerID($name, $summonerdata) {
		return $summonerdata[$name]['id'];
	}
	
	/*
		Name: getSummonerName
		Arguments: name (string), summonerdata (array)
		Returns: level (string)
		Desc: Returns the level of the summoner
	*/
	function getSummonerName($name, $summonerdata) {
		return $summonerdata[$name]['name'];
	}
	
	/*
		Name: getSummonerLevel
		Arguments: name (string), summonerdata (array)
		Returns: level (string)
		Desc: Returns the level of the summoner
	*/
	function getSummonerLevel($name, $summonerdata) {
		return $summonerdata[$name]['summonerLevel'];
	}
	
	/*
		Name: getSummonerAvatar
		Arguments: name (string), summonerdata(array)
		Returns: avatarid (string)
		Desc: Returns the avatar of the player
	*/
	function getSummonerAvatar($name) {
		return $summonerdata[$name]['profileIconId'];
	}
?>
