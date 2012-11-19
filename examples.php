<?php
/*
 *
 * Example use of twitch_tv_api() ; covers most of what exists in the documentation
 * chat methods are currently not supported (having some issues with some 401 stuff) as
 * well as other methods that require authentication.
 */

// Making an object , but can be called like twitch_tv_api::method();
require('twitch_tv_api.php');
$obj = new twitch_tv_api();

// streams featured
var_dump($obj::test($obj::streams_featured()));

// streams featured limit 1 offset 1
var_dump($obj::test($obj::streams_featured(1,1)));

// streams featured, limit 1
var_dump($obj::test($obj::streams_featured(1)));

// streams sumary (all)
var_dump($obj::test($obj::streams_summary()));

// streams summary of user 'hebo'
var_dump($obj::test($obj::streams_summary('hebo')));

// passes argument to 'streams' as 'channel' get param
var_dump($obj::test($obj::streams('incredibleorb,incontrolt')));

// passes 'hebo' as channel get param and other methods streams('channel','limit','offset','game','embeddable')
var_dump($obj::test($obj::streams('hebo')));

// bizarre function that doesnt fit into the algorithm (get a channels status)
var_dump($obj::test($obj::streams_channel('hebo')));

// Get 'top games' limit 10 offset 10
var_dump($obj::test($obj::games_top(10,10)));

// Get single video by argument value
var_dump($obj::test($obj::videos('a328087483')));

// search streams with query, limit 1 offset 1
var_dump($obj::test($obj::search_streams('game',1,1)));

// search games (must pass it a 'suggest' as second param! (Working on it..)
var_dump($obj::test($obj::search_games('a game','suggest',1)));

// get a single video
var_dump($obj::test($obj::videos('a328087483')));

// gets channels of towelliee with limit/offset
var_dump($obj::test($obj::channels('towelliee',100,10)));

// gets user 'hebo'
var_dump($obj::test($obj::users('hebo')));

// teams by 'eg'
var_dump($obj::test($obj::teams('eg')));

// Invalid URL Fail
var_dump($obj::test($obj::videos('mehhh')));
// Base url not in query Fail
var_dump($obj::test('google'));