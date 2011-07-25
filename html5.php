<?php

include_once('class_tag.php');

$tag = new tag();

$tag->add_text('<!doctype html>')
	->add_comment('paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/')
	->add_comment('[if lt IE 7]> <html class="no-js ie6" lang="en"> <![endif]')
	->add_comment('[if IE 7]>    <html class="no-js ie7" lang="en"> <![endif]')
	->add_comment('[if IE 8]>    <html class="no-js ie8" lang="en"> <![endif]')
	->add_comment('[if gt IE 8]><!-->  <html class="no-js" lang="en"> <!--<![endif]');
$head = $tag->head();
$body = $tag->body();
$tag->add_text('</html>');

$title = $head->meta('charset','utf-8')->_p()
	 		  ->meta('http-equiv', 'X-UA-Compatible', 'content', 'IE=edge,chrome=1')->_p()
			  ->title();

$head->meta('name','discription','content',NULL)->_p()
	 ->meta('name','author', 'content', NULL);


echo $tag->display('pretty');
