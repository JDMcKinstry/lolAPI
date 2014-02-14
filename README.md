lolAPI
=

A simple PHP Class for interacting with Riot's LoL REST API found @ https://developer.riotgames.com.

*It should be noted, this is not yet intended to be a "complete library", but rather a simple and easy to update class that allows ease of use to Riot's LoL REST API, thus allowing the programmer "Freedom" to create.*

Thus far, the class has 2 simple parts. The first is a set of functions simply for making a correct link and using CURL to submit that link. The second is just a set of methods that refelect the different REST calls, with some parameters that make using the previously mentioned functions much easier.

ONLY ONE FILE NEEDED! -> https://raw.github.com/JDMcKinstry/lolAPI/master/lolAPI.php

**Updating the Class** -> This should be relativly simple. As you'll notice when you look at the class, the top section is labeled `/*	Init Construction	*/`. Here is where you can adjust the "*Rate Limits*" for a piece I'm working on which will keep you from *overcalling*. 75+% of updating will simply be in the property `private $apiUrls`. Simply add, or update links as needed and version numbers. Only the latest version number is needed, however, through params, programmers can easily make use of older versions if needed. The rest may simply be writting a new "simple" method if new methods are added to the REST API. This simple maintenance plan makes adding this to any other "extended" LoL API Library or even direct interface easy to update and maintain. The rest of your work is up to you!

*Update 01/31/2014 1500* After making more use of this myself, I decided to incorporate some simple methods for getting JSON data from Data Dragon. For now, all that's needed to maintain it is to update the variable `private (array) $dataDragonNfo`.

*Coming Soon*
 - Rate Limiting

Requires CURL
=
Make sure you have curl enabled. If you don't know how to enable curl, try the following resources.

 - http://www.php.net/manual/en/curl.installation.php
 - https://php.net/curl
 - http://www.tomjepson.co.uk/enabling-curl-in-php-php-ini-wamp-xamp-ubuntu/
 - http://stackoverflow.com/questions/13021536/how-to-enable-curl-in-wamp-server
 - http://stackoverflow.com/questions/1347146/how-to-enable-curl-in-php-xampp
 - http://bit.ly/1kHkMAo

¡IMPORTANT!
=
You have the option to set your API KEY permnently to your file. Simply open the `lolAPI` class in an editor and look for `/*	Init Construction	*/` at the very top. Simply set your API KEY in the quotes by `const API_KEY = '';`. 

	class lolAPI {
		/*	Init Construction	*/
		const API_KEY = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';

|OR|

Keep in mind, this step is optional, however, if you do not do this, then every time you call a new instance of the class, you will be required to provide your `RIOT LoL API KEY` everytime you establish new instance, such as:

	$lolAPI = new lolAPI('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');

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


EXAMPLES (*Data Dragon*)
=

	$x = new lolAPI();
	
	$ddChampion = $x->ddChampionJSON();
	$ddItem = $x->ddItemJSON();
	$ddMastery = $x->ddMasteryJSON();
	$ddRune = $x->ddRuneJSON();
	$ddSummoner = $x->ddSummonerJSON();
