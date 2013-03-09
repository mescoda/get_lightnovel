<?php

$url = 'http://lknovel.lightnovel.cn/main/vollist/178.html';
// $url = 'http://lknovel.lightnovel.cn/main/book/890.html';

if(strpos($url, 'view')) {
	$type = 'chapter';
} elseif(strpos($url, 'book')) {
	$type = 'book';
} elseif(strpos($url, 'vollist')) {
	$type = 'series';
}

switch($type) {
	case 'chapter':
		get_chapter($url);
		break;
	case 'book':
		get_book($url);
		break;
	case 'series':
		get_series($url);
		break;
}

function get_chapter($url) {
	$chapter_html =  file_get_contents($url);
	preg_match('/<h2(?:.+)>(.+)<\/h2>/', $chapter_html, $book_title);
	preg_match('/<h3(?:.+)>(.+)<\/h3>/', $chapter_html, $chapter_title);
	$title = $book_title[1].'-'.$chapter_title[1];
	$title = preg_replace('/\s+/', '-', $title);
	$file_name = mb_convert_encoding($title, 'gbk', 'utf8');

	$raw_pattern = '/<div\s+id="J_view"(?:.+)>([\s\S]+?)<div\s+class="text-center/';
	preg_match($raw_pattern, $chapter_html, $content_html);

	$patterns = array('/^\s+|\s+$/', '/>(\s+?)</', '/<br \/>/', '/<\/(.+?)>/', '/<(.+?)>/');
	$replacements = array('', '><', '', "\r\n", '');

	$output = preg_replace($patterns, $replacements, $content_html[1]);

	// $file = fopen("$file_name.txt", 'a+');
	// fwrite($file, $output);
	// fclose($file);
	echo "$title done \n";
	return $output;

	// file_put_contents("$file_name.txt", $output);
	
}

function get_book($url) {
	$book_links = array();
	$book_content = '';
	$book_html = file_get_contents($url);
	preg_match('/<h1 class="ft-24">(?:[\s\S]+)<strong>([\s\S]+?)<\/strong>/', $book_html, $title_match);
	$title = $title_match[1];
	$title = preg_replace('/^\s+|\s+$/', '', $title);
	$title = preg_replace('/&nbsp;/', '', $title);
	$title = preg_replace('/\s+/', '-', $title);
	$file_name = mb_convert_encoding($title, 'gbk', 'utf8');

	preg_match_all('/<li class="span3">([\s\S]+?)<\/li>/', $book_html, $book_link_match);
	foreach($book_link_match[1] as $item) {
		preg_match('/href="(.+?)"/', $item, $link);
		array_push($book_links, $link[1]);
	}
	foreach($book_links as $link) {
		$book_content .= get_chapter('http://lknovel.lightnovel.cn'.$link);
	}
	file_put_contents("$file_name.txt", $book_content);
	echo "$title whole done \n";
}

function get_series($url) {
	$series_links = array();
	$series_html = file_get_contents($url);
	preg_match_all('/<h2 class="ft-24">(?:[\s\S]+?)<strong>([\s\S]+?)<\/strong>/', $series_html, $series_link_match);
	foreach($series_link_match[1] as $item) {
		preg_match('/href="(.+?)"/', $item, $link);
		array_push($series_links, $link[1]);
	}
	foreach($series_links as $book_link) {
		get_book('http://lknovel.lightnovel.cn'.$book_link);
	}
	echo "series done \n";
}

?>