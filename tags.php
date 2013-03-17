<?php

function do_post_request($data, $optional_headers = null)
{
	$url = "http://api.search.yahoo.com/ContentAnalysisService/V1/termExtraction";
	$params = array('http' => array(
              'method' => 'POST',
              'content' => $data
	));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		throw new Exception("Problem with $url, $php_errormsg");
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		throw new Exception("Problem reading data from $url, $php_errormsg");
	}
	return $response;
}

function do_curl_request($data) {
	// The request URL prefix

	$request =  'http://search.yahooapis.com/ContentAnalysisService/V1/termExtraction';
	// The request parameters
	$appid = $data['appid'];
	$context = $data['context'];
	//$query = 'madonna';
	// urlencode and concatenate the POST arguments
	$postargs = 'appid='.$appid.'&context='.urlencode($context).'&show_metadata=true';

	$session = curl_init($request);

	// Tell curl to use HTTP POST
	curl_setopt ($session, CURLOPT_POST, true);
	// Tell curl that this is the body of the POST
	curl_setopt ($session, CURLOPT_POSTFIELDS, $postargs);
	// Tell curl not to return headers, but do return the response
	curl_setopt($session, CURLOPT_HEADER, false);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($session);
	curl_close($session);

	return $response;

	/*$request =  'http://query.yahooapis.com/v1/public/yql';
	 // The request parameters
	$appid = $data['appid'];
	$context = $data['context'];
	//$query = 'madonna';
	// urlencode and concatenate the POST arguments
	//$postargs = 'appid='.$appid.'&context='.urlencode($context).'&show_metadata=true';

	$yql_query = "select * from contentanalysis.analyze where context='$context'";

	$request = $request . "?q=" . urlencode($yql_query);

	$session = curl_init($request);

	// Tell curl to use HTTP POST
	//curl_setopt ($session, CURLOPT_POST, true);
	// Tell curl that this is the body of the POST
	//curl_setopt ($session, CURLOPT_POSTFIELDS, $postargs);
	// Tell curl not to return headers, but do return the response
	//curl_setopt($session, CURLOPT_HEADER, false);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($session);
	curl_close($session);

	return $response;*/

}

function extractTagsFromWebResponse($webResponse) {
	$resultSet = new SimpleXMLElement($webResponse);

	$tags = array();
	foreach($resultSet->Result as $v) {
		array_push($tags, (string)$v);
	}

	return $tags;
}

function frequencyBasedTags($txt) {

	$commonWords = array('a','able','about','above','abroad','according','accordingly',
	'across','actually','adj','after','afterwards','again','against','ago',
	'ahead','ain\'t','all','allow','allows','almost','alone','along',
	'alongside','already','also','although','always','am','amid',
	'amidst','among','amongst','an','and','another','any','anybody',
	'anyhow','anyone','anything','anyway','anyways','anywhere','apart',
	'appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking',
	'associated','at','available','away','awfully','b','back','backward','backwards','be','became',
	'because','become','becomes','becoming','been','before','beforehand','begin','behind','being',
	'believe','below','beside','besides','best','better','between','beyond','both','brief','but','by',
	'c','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes',
	'clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider',
	'considering','contain','containing','contains','corresponding','could','couldn\'t','course',
	'c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t',
	'different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during',
	'e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough',
	'entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone',
	'everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves','you\'ve','z','zero');


	$txt = strtolower($txt);
	$txt =  preg_replace('/\b('.implode('|',$commonWords).')\b/','',$txt);

	$tags = explode(" ", $txt);
	$freq = array();
	foreach ($tags as $t) {
		if(!($freq[$t])) {
			$freq[$t] = 0;
		}
		$freq[$t] = $freq[$t]+1;
	}

	arsort($freq);

	$max_tags = 25;
	$ret = array();
	foreach($freq as $tag => $count) {

		if(!ctype_alpha($tag))
			continue;

		array_push($ret, $tag);

		$max_tags--;
		if($max_tags == 0)
			break;
	}

	return $ret;
}

?>