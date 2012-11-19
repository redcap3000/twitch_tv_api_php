<?php 
/* Twitch TV API (PHP)
 *
 * Ronaldo Barbachano (doinglines.com) Nov. 2012
 * A polymorphic query generator defined by '$twitch'. Assoc. elements with keys 
 * define 'sub functions' (function_subfunction).	With a few extra callouts on 
 * specific methods that have different GET param's (search, search_games) or a
 * differing url structure.
 */
class twitch_tv_api{
	
	const baseurl = 'https://api.twitch.tv/kraken/';
	
	public static function __callStatic($names,$arguments){
		if($names == 'test' && isset($arguments[0])){
		// Method that can test a url and return json or the error	
			// ensure the base url exists ...
			if( strpos($arguments[0], self::baseurl) === 0){
				// attempt to decode use @ to 'ignore the warnings
				if( !$r = @file_get_contents($arguments[0]))
					$r = error_get_last();
				else{					
					$z = @json_decode($r);
					if($z ===0) {
					// catch json decode error
						$r = error_get_last();
						unset($z);					
					}
				}	
			}
			else{
				$r = 'Base url not in query';
			}	
			// make sure $r is not false
			return (isset($z) ? array('query'=>$arguments[0],'json'=>$z) : array('query'=>$arguments[0],'response'=>$r) );	
		
		}
		
		/* $twitch array defines the structure of the api, allowable method/sub 
		 * method calls and parameter orders.
		 * allowable static method calls, and sub calls (search_games, streams_summary, etc)
		 * Submethods are embeded beneath its method and defined by array keynames. 
		 * If a sub method value is an array then it define
		 * the order and name of the _GET parameters : 
		 * ex: games_top(offset,limit) ; = games=>array('top'=>array('offset','limit');
		 * elements without follow method('directive',limit,offset), where directive is the last 
		 * part of the URL before the _GET query.
		 */
		$twitch = array(
	   		'channels',
	   		'users'=>array('users'=>''),
	   		'videos'=> array('videos'=>''),
	   		'games' => array( 'top'=>array('offset','limit')) ,
	   		// streams(game,channel,limit,offset) -- test this.. might not work as planned...
			'streams'=> array(
			// properly support /streams/:channel, and other methods that do not have get params by setting
			// that key value to a blank string
							'channel'=>'',
							'streams'=>array('channel','limit','offset','game','embeddable'),
							'summary'=>array('game'),
							'featured'=>array('limit','offset')),
			// ::search_games(query,live=true,limit,offset,type=suggest) and ::search_streams(query,limit,offset)
			'search'=>	array(
							'games'=>array('q','type','live'),
							'streams'=>array('q','limit','offset')
							),
							
			// doesn't support limit/offset...				
			'teams'=>array('teams'=>''),
			);
		
		if($names == 'streams' && count($arguments) == 1){
		// streams doesnt 'play nice'
			return self::baseurl . $names . '?'.http_build_query(array('channel'=>$arguments[0]));
		}
		
		if(strpos($names, '_',1)){
		/*
		 * check $names for underscores and when found set $names and $terms to those values (only accept one...)
		 * ignore top_games method
		 */
			$names_e = explode('_',$names, 2);
			// weird syntax to conform to the streams_channel method to check if a channel is online
			if($names_e[0] == 'streams' && $names_e[1] == 'channel' && isset($arguments[0]) && is_string($arguments[0]) && trim($arguments[0]) != ''){
				$found = true;
				// set the last item of the URL to the value passed via first method arg
				$term = $arguments[0];
				$names_e = $names_e[0];
			}elseif(array_key_exists($names_e[0], $twitch) &&( isset($twitch[$names_e[0]])&& array_key_exists($names_e[1],$twitch[$names_e[0]]))){
				/*
				 * check that function syntax exists and is valid modify function with new values..
				 *
				 */
				$term = $names_e[1];
				$names_e = $names_e[0];
				/* 
				 * processing a sub method call as triggered by setting of $names_e (names exploded) 
				 * and its existance in the array and sub array (could use work.. maybe)
				 *
				 */
				foreach($twitch[$names_e] as $loc=>$item){
				// check keys within elements
					if(!is_numeric($loc) && $loc == $term || $loc == $names){	
					/*  Array structure verification...
					 *  if its properly formed (key containing array)
					 *	this is a custom defined directive allow for submethods without any passed (get) parameters
					 */	
						if(is_array($item) && !empty($item) && count($item) == count($arguments)){
							$params = array_combine($item,$arguments);	
						}else{
							foreach($arguments as $loc=>$value){
								if(isset($item[$loc]) && !is_numeric($item[$loc]) && $item[$loc] != '')
									$params[$item[$loc]] = $value;
							}
						}
						// variable to avoid reprocessing term/limit/offset
						$found = true;
					}
					elseif($loc != $term) {
						// catch an improperly formed api reference array ($twitch) (or non existance)
						$not_found = true;
				   			
					}
				}
			}else{
			// sub method not found
				return false;
			}
		}

		if(!isset($found))
		// call out for not processing defaults within a submethod
			if( in_array($names,$twitch)  || (isset($term) && isset($names_e) && array_key_exists($term, $twitch[$names_e])) ){
		 	/*
		 	 * process default parameter order for methods methods without a defined parameter list
		 	 * We use !isset($found) incase we are working with a sub query that did not have an array parameter list defined
		 	 * method(term,limit,offset)
		 	 *
		 	 */
		 	 	// build term from first argument
		 	 	$term = '/'.$arguments[0];
		 	 	// do paginations
			 	$params['limit'] = (isset($arguments[1]) ? $arguments[1]: 100);
			 	$params['offset'] = (isset($arguments[2])?$arguments[2] : 0);
		 	}elseif(isset($arguments[0])){
		 		// process a method that only has one argument that is passed in the URL (videos(video id) is an example)
		 		$term = '/'.$arguments[0];
		 	}
  	/*
  	 * 
  	 * Assemble URL with baseurl class const, names_e (if its a sub method) or $names, and 'term' if set + get parameters 
  	 *
  	 */
  	  	return (is_string($names)?self::baseurl.(isset($names_e) && isset($term)? $names_e.'/'.$term:$names).(isset($term)  && !isset($names_e) ?$term :''). (!empty($params) ? '?'.http_build_query($params) : '') :false);
	}
}