<?php
	/**	CLASS lolAPI
	 *	@created 01/01/2014
	 *	@author JD McKinstry <jdmckinstry@gmail.com>
	 *	@uses CURL
	 *	@copyright Copyright (c) 2014, J.D. McKinstry
	 *	@license https://github.com/JDMcKinstry/lolAPI/blob/master/LICENSE
	 *	@modified 01/30/2014
	 *	@modifiedby JD McKinstry
	 *	@contributions
	 *	 - awaiting contributions
	 */
	class lolAPI {
		/*	Init Construction	*/
		const API_KEY = '';
		private $apiKey;
		function __construct($apiKey=self::API_KEY) {
			$this->apiKey = $apiKey;
			if (empty($this->apiKey)) return "Please provide an API Key.";
		}
		
		/*	Constants	*/
		const BASE_URL = 'https://prod.api.pvp.net';
		const FAIL = 'FAIL';
		const SUCCESS = 'SUCCESS';
		
		/*	Properties:Private	*/
		private $apiUrls = array(
			'champion' => array(
				'url' => '/api/lol/{region}/{version}/champion',
				'ver' => '1.1'
			),
			'game' => array(
				'url' => '/api/lol/{region}/{version}/game/by-summoner/{summonerID}/recent',
				'ver' => '1.3'
			),
			'league' => array(
				'url' => '/api/lol/{region}/{version}/league/by-summoner/{summonerID}',
				'ver' => '2.3'
			),
			'static-data' => array(
				'url' => array (
					'champion' => '/api/lol/static-data/{region}/{version}/champion/{id}',
					'item' => '/api/lol/static-data/{region}/{version}/item/{id}',
					'mastery' => '/api/lol/static-data/{region}/{version}/mastery/{id}',
					'realm' => '/api/lol/static-data/{region}/{version}/realm',
					'rune' => '/api/lol/static-data/{region}/{version}/rune/{id}',
					'spell' => '/api/lol/static-data/{region}/{version}/summoner-spell/{id}',
					'summoner-spell' => '/api/lol/static-data/{region}/{version}/summoner-spell/{id}'
				),
				'ver' => '1'
			),
			'stats' => array(
				'url' => array (
					'summary' => '/api/lol/{region}/{version}/stats/by-summoner/{summonerID}/summary',
					'ranked' => '/api/lol/{region}/{version}/stats/by-summoner/{summonerID}/ranked'
				),
				'ver' => '1.2'
			),
			'summoner' => array(
				'url' => array (
					'by-id' => '/api/lol/{region}/{version}/summoner/{summonerID}',
					'by-name' => '/api/lol/{region}/{version}/summoner/by-name/{summonerName}',
					'name' => '/api/lol/{region}/{version}/summoner/{summonerID}/name',
					'masteries' => '/api/lol/{region}/{version}/summoner/{summonerID}/masteries',
					'runes' => '/api/lol/{region}/{version}/summoner/{summonerID}/runes',
				),
				'ver' => '1.3'
			),
			'team' => array(
				'url' => '/api/lol/{region}/{version}/team/by-summoner/{summonerID}',
				'ver' => '2.2'
			)
		);
		
		private $errorCodes = array(
			200 => 'SUCCESS',
			400 => 'Bad request',
			//401 => 'Missing api key',
			401 => 'Unauthorized',
			404 => 'Summoner not found',
			500 => 'Internal server error',
			503 => 'Internal server error'
		);
		
		private $regions = array(	//	BR, EUNE, EUW, KR, LAN, LAS, NA, OCE, RU, TR
			'br' => 'Brazil',
			'eune' => 'Eastern Europe',
			'euw' => 'Western Europe',
			'kr' => 'Korea',
			'lan' => '',
			'las' => '',
			'na' => 'North America',
			'oce' => '',
			'ru' => '',
			'tr' => 'Tournament'
		);
		
		/*	Methods:Public:Utility	*/
		/**	callAPI(string, $url [, array $data]);
		 *	@param STRING $url Must have URL to try to CURL
		 *	@param ARRAY Data to add to query string for CURL call
		 *	@return ARRAY Results from CURL call
		 */
		public function callAPI($url, $data=array()) {
			$query = '?api_key=' . $this->API_KEY;
			if (!empty($data)) $query .= '&' . (is_array($data) ? http_build_query($data) : $data);
			
			if (!empty($url)) {
				$curlOpts = array(
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_TIMEOUT => 3,
					CURLOPT_URL => $url.$query,
					CURLOPT_VERBOSE => 1
				);
				
				$ch = curl_init($url);
				curl_setopt_array($ch, $curlOpts);
				$response = curl_exec($ch);
				$result = $this->formatResult($ch, $response);
				curl_close($ch);
				
				return $this->result = $result;
			}
		}
		
		/**	getRegions([bool $fullNames]);
		 *	@param BOOLEAN $fullNames If set to TRUE, will return an array of Server Names instead of abbriviations
		 *	@return ARRAY List of server abbriviations
		 */
		public function getRegions($fullNames=FALSE) { return $fullNames ? $this->regions : array_keys($this->regions); }
		
		/**	getRegions([string $value, bool $strict]);
		 *	@note Does not have to be exact. Only requires $value to match Abbriviations by 80% or Names by 90%.
		 *	@param STRING $value The name of the server you want the abbriviation for |OR| the abbriviation you want the server name for
		 *	@param BOOLEAN $strict If set to true, then $value must be an exact match in Server Name or Abbriviation
		 *	@return STRING If your value is an abbriviation, will return Name, or vice versa.
		 *	@return DEFAULT The current abbriviation for the North American Server
		 */
		public function getRegion($value=NULL, $strict=FALSE) {
			if (!empty($value)) {
				if (key_exists($value, $this->regions)) return $this->regions[$value];
				if ($strict) {
					$key = array_search($value, array_map('strtolower',$this->regions));
					return is_string($key) ? $key : FALSE;
				}
				else $value = strtolower($value);
				$key = array_search($value, array_map('strtolower',$this->regions)); 
				if (is_string($key)) return $key;
				foreach($this->regions as $k => $v) {
					similar_text($k, $value, $kPerc);
					if ($kPerc >= 80) return $v;
					similar_text(strtolower($v), $value, $vPerc);
					if ($vPerc >= 90) return $k;
				}
				return FALSE;
			}
			return self::getRegion('North America');
		}
		
		/**	getUrl(string $key, string $id, [, string $subKey, string $region, string $version ]);
		 *	@param STRING $key the method name you seek to use from the LoL REST API
		 *	@param STRING $id may be a summoner's name or id, or an item id
		 *	@param STRING $subKey if there is a sub-method, such as seen on: /api/lol/static-data/{region}/v1/mastery <-- "mastery" is the submethod
		 *	@param STRING $region server to try to pull from
		 *	@param STRING $version version of method to use, if not set, will use latest version set in url's property
		 *	@return STRING Returns url for use with LoL REST API
		 */
		public function getUrl($key, $id, $subKey=NULL, $region=NULL, $version=NULL) {
			if (key_exists($key, $this->apiUrls)) {
				$url = $verifyUrl = $this->apiUrls[$key]['url'];
				if (is_array($url) && is_string($subKey)) {
					if (key_exists($subKey, $url)) $verifyUrl = $url = $url[$subKey];
					else return FALSE;
				}
				if (is_null($version)) $version = $this->apiUrls[$key]['ver'];
				if (!key_exists($region, $this->regions)) $region = self::getRegion($region);
				if (is_string($url) && key_exists($region, $this->regions) && !is_null($version)) {
					$url = str_replace('{version}', "v$version", str_replace('{region}', $region, $url));
					if (!empty($id)) $url = preg_replace('/{(summonerID|summonerName|id)}/', $id, $url);
					$url = preg_replace('[//]', '/', preg_replace('/{(.*?)}/', '', $url));
					$verifyUrl = preg_replace('/{(.*?)}/', '(.*?)', $verifyUrl);
					if (substr($verifyUrl, strlen($verifyUrl)-6) == '/(.*?)') $verifyUrl = substr($verifyUrl, 0, -6) . '[/\d\w\s]*';
					if(substr($url, -1) == '/') $url = substr($url, 0, -1);
					return preg_match('['.$verifyUrl.']', $url) ? self::BASE_URL.$url : FALSE;
				}
			}
			return FALSE;
		}
		
		/*	Methods:Public:Basic	*/
		/**	champion(bool $freeToPlay, string $region, string $version)
		 *	@return ARRAY Retrieve all champions
		 */
		public function champion($freeToPlay=FALSE, $region=NULL, $version=NULL) { return self::callAPI(self::getUrl('champion', NULL, NULL, $region, $version), array( 'freeToPlay' => $freeToPlay == TRUE ? 'true' : 'false' )); }
		
		/**	game(long $id, string $region, string $version)
		 *	@return ARRAY Get recent games by summoner ID
		 */
		public function game($id, $region=NULL, $version=NULL) { return self::callAPI(self::getUrl('game', $id, NULL, $region, $version)); }
		
		/**	champion(long $id, string $region, string $version)
		 *	@return ARRAY Retrieves leagues data for summoner, including leagues for all of summoner's teams
		 */
		public function league($id, $region=NULL, $version=NULL) { return self::callAPI(self::getUrl('league', $id, NULL, $region, $version)); }
		
		/**	champion(long $id, string $region, string $version)
		 *	@return ARRAY "champion" Retrieves champion list.
		 *	@return ARRAY "champion/{id}" Retrieves a champion by its id.
		 *	@return ARRAY "item" Retrieves item list.
		 *	@return ARRAY "item/{id}" Retrieves item by its unique id.
		 *	@return ARRAY "mastery" Retrieves mastery list.
		 *	@return ARRAY "mastery/{id}" Retrieves mastery item by its unique id.
		 *	@return ARRAY "realm" Retrieve realm data.
		 *	@return ARRAY "rune" Retrieves rune list.
		 *	@return ARRAY "rune/{id}" Retrieves rune by its unique id.
		 *	@return ARRAY "summoner-spell" Retrieves summoner spell list.
		 *	@return ARRAY "summoner-spell/{id}" Retrieves summoner spell by its unique id.
		 */
		public function staticData($subKey, $id=NULL, $data=NULL, $region=NULL, $version=NULL) { return self::callAPI(self::getUrl('static-data', $id, $subKey, $region, $version), $data); }
		
		/**	champion(long $id, string $region, string $version)
		 *	@return ARRAY "summary" Get player stats summaries by summoner ID. One summary is returned per queue type.
		 *	@return ARRAY "ranked" Get ranked stats by summoner ID. Includes statistics for Twisted Treeline and Summoner's Rift.
		 */
		public function stats($id, $subKey, $data=NULL, $region=NULL, $version=NULL) { return self::callAPI(self::getUrl('stats', $id, $subKey, $region, $version), $data); }
		
		/**	champion(long $id, string $region, string $version)
		 *	@return ARRAY "by-id" Get summoner objects mapped by summoner ID for a given list of summoner IDs
		 *	@return ARRAY "by-name" Get summoner objects mapped by standardized summoner name for a given list of summoner names 
		 *	@return ARRAY "name" Get summoner names mapped by summoner ID for a given list of summoner IDs
		 *	@return ARRAY "masteries" Get mastery pages mapped by summoner ID for a given list of summoner IDs
		 *	@return ARRAY "runes" Get rune pages mapped by summoner ID for a given list of summoner IDs
		 */
		public function summoner($id, $subKey, $data=NULL, $region=NULL, $version=NULL) { return self::callAPI(self::getUrl('static-data', $id, $subKey, $region, $version), $data); }
		
		/**	champion(long $id, string $region, string $version)
		 *	@return ARRAY Retrieves teams for given summoner ID
		 */
		public function team($id, $region=NULL, $version=NULL) { return self::callAPI(self::getUrl('game', $id, NULL, $region, $version)); }
		
		/*	Methods:Private:Utility	*/
		private function formatResult($ch, $response) {
			$curlNfo = curl_getinfo($ch);
			$errCode = curl_errno($ch);
			
			if ($errCode) {
				$errMsg = curl_error($ch);
				$result = array(
					'status' => self::FAIL,
					'code' => $errCode,
					'curlNfo' => $curlNfo,
					'msg' => $errMsg
				);
			}
			else {
				$objResponse = json_decode($response);
				if (empty($objResponse)) {
					$errCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
					$errMsg = $this->errorCodes[$errCode];
					
					$result = array(
						'status' => self::FAIL,
						'code' => $errCode,
						'curlNfo' => $curlNfo,
						'msg' => $errMsg
					);
				}
				else {
					$araResponse = $this->objToArr($objResponse);
					
					$result = array(
						'status' => self::SUCCESS,
						'curlNfo' => $curlNfo,
						'response' => $araResponse
					);
					
					if (isset($araResponse['status'])) {
						if (isset($araResponse['status']['status_code'])) {
							$result = array(
								'status' => self::FAIL,
								'code' => $araResponse['status']['status_code'],
								'curlNfo' => $curlNfo,
								'msg' => $araResponse['status']['message']
							);
						}
					}
					
				}
			}
			
			return $result;
		}
		
		private function objToArr($obj) {
			$ret = array();
			foreach ($obj as $k => $v) {
				if (is_array($v) || is_object($v)) $v = $this->objToArr($v);
				$ret[$k] = $v;
			}
			return $ret;
		}
		
	}
?>
