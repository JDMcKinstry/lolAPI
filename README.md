lolAPI
=

A simple PHP Class for interacting with Riot's LoL REST API found @ https://developer.riotgames.com

Thus far, the class has 2 simple parts. The first is a set of functions simply for making a correct link and using CURL to submit that link. The second is just a set of methods that refelect the different REST calls, with some parameters that make using the previously mentioned functions much easier.

ONLY ONE FILE NEEDED! -> https://github.com/JDMcKinstry/lolAPI/blob/master/lolAPI.php

¡IMPORTANT!
=
You have the option to set your API KEY permnently to your file. Simply open the `lolAPI` class in an editor and look for `/*	Init Construction	*/` at the very top. Simply set your API KEY in the quotes by `const API_KEY = '';`. 

Keep in mind, this step is optional, however, if you do not do this, then every time you call a new instance of the class, you will be required to provide you `RIOT LoL API KEY`.

EXAMPLES (*Basic Methods*)
=

	//	gets an array of region abbriviations or full names
	$lolAPI->getRegions();
-
	//	get a region abbriv or full name based on passed string.
	//	Abbriviations only need match 80% while full names must be at least 80% correct
	$lolAPI->getRegion('north america');
-
	//	helps to make a proper Riot LoL REST URL
	$lolAPI->getUrl($RESTCommand, $idORname, $possibleSubCommand, $region, $version);
-
	//	helps to call Riot's REST Server if the url is correct
	//	best to submit data in the form of: array( 'key' => 'value', 'key2' => 'value2' )
	$lolAPI->callAPI($url, $possibleQueryData);

EXAMPLES (*LoL REST Methods*)
=

	//	gets an array of region abbriviations or full names
	$lolAPI->getRegions();
-
	//	The variable here decides if to get F2P only champs or not
	//	if no param, will return all champs
	$lolAPI->champion(TRUE);
-
	//	must include Summoner ID
	$lolAPI->game($summonerID);
	$lolAPI->league($summonerID);
	$lolAPI->team($summonerID);
-
	//	must include a "subkey"
	//	subkey is considered the method called by the link, aka, the last part of the url
	$lolAPI->stats($summonerID, 'summary');
	$lolAPI->summoner($summonerID, 'runes');
-
	//	summoner comes with 2 small difs, subkey's personally defined based on 'by id' or 'by name'
	//	NOTE: these have nothing to do with other summoner subkeys such as "name" which requires the 'id' already
	$lolAPI->summoner($summonerID, 'by-id');
	$lolAPI->summoner($summonerID, 'by-name');
-
	//	make sure to read the comments when making this next call
	//	it has plenty of "subkeys", all added to method comment for ease of use in IDE's
	$lolAPI->staticData('champion', $id);
	//	don't forget you can include query data as an array (OptionaL 3rd param) 
