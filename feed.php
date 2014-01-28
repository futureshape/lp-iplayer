<?php

$skip_keywords = array('TV', 'Guidance');

$feed_contents = file_get_contents("http://feeds.bbc.co.uk/iplayer/highlights/tv");

$xml_root = simplexml_load_string($feed_contents);

$tidy_config = array(
    'indent' => true,
    'clean' => true,
    'input-xml'  => true,
    'output-xml' => true,
    'wrap'       => false
    );

$tidy = new Tidy();

$programmes = array();

foreach ($xml_root->entry as $entry) {

	$programme = array();
	$programme['title'] = trim((string) $entry->title);
	$content_xml = '<?xml version="1.0" encoding="utf-8"?><content>' . (string) $entry->content . '</content>';
	$content_root = simplexml_load_string($tidy->repairString($content_xml, $tidy_config));
	$programme['description'] = trim((string) $content_root->p[1]);
	$programme['thumbnail'] = trim((string) $content_root->p[0]->a->img['src']);
	$programme['keywords'] = array();
	
	foreach ($entry->category as $category) {
		$keyword =  trim((string)$category['term']);
		if(!in_array($keyword, $skip_keywords))	
			$programme['keywords'][] = $keyword;
	}

	$programmes[] = $programme; 
}

header("Content-Type: text/html; charset=utf-8");
header('ETag: "' . md5($feed_contents) . '"');

?>

<html>
<head>
        <meta charset="utf-8">
        <style type="text/css">
                * { margin: 0; padding: 0; font-family:'Cabin'; }
                img { float:left; margin-right: 10px;}
                h1 { margin-bottom: 10px; padding-top: 10px; font-size: 22px; clear:both;}
                p { font-size: 18px; margin-top: -4px;}
                body { width: 384px; padding-top: 10px; padding-bottom: 10px;}
                ul { list-style-type: none }
                li {  font-size: 18px;
                        background-image: url(http://futureshape.net/lp-iplayer/tv.png);
                        background-repeat: no-repeat;
                        background-position: 0px 0px; 
                        padding-left: 20px; display: inline;
                }
                li:nth-child(even) {
                        font-weight:bold;
                }
                .category { white-space:nowrap; background-color: black; color: white; border-radius: 10px; padding-left: 5px; padding-right: 5px; font-size: 15px; font-weight: bold; font-family: 'Arial'}
        </style>
</head>
<body>
<div>
<img src="http://futureshape.net/lp-iplayer/header.png">
<?php for($i = 0; $i < 3; $i++) { ?>
<h1><?php echo $programmes[$i]['title']; ?></h1>
<img src="<?php echo $programmes[$i]['thumbnail'];?>" class="dither"/> 
<p><?php echo htmlentities($programmes[$i]['description']) . " "; foreach ($programmes[$i]['keywords'] as $keyword) { ?>
<span class="category"><?php echo htmlentities($keyword) ?></span>
<?php } ?></p>
<?php } ?>

<h1>Also on:</h1>
<ul>
<?php for($i = 3; $i < count($programmes); $i++) { ?>
<li><?php echo htmlentities($programmes[$i]['title']); ?></li>
<?php } ?>
</ul>
</div>
</body>
</html>