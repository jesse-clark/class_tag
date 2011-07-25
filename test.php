<?php

include_once('class_tag.php');

$tag = new tag();

// $html = $tag->add_tag('html');
// $html -> add_tag('head');
// $html -> add_tag('body')->add_tag('div1')->parent()->add_tag('div2') -> add_tag('div2a') -> add_text('pasta');
// $html->set_attributes();
// 
// //var_dump($tag);

$st = $tag->html()->head()->parent()->body()->div('class','1')->_p()->div('class','2')->div('class', '2a')->add_text('pasta');
$st->div('class', 'something', 'somethingelse')->add_text('more pasta');
$st->div()->add_text('hahahah');

$class = $tag->search_attributes('class');

//var_dump($class);

// foreach($class as $class_tags) {
// 	//var_dump($tag);
// 	foreach($class_tags as $tagr) {
// 		if( $tagr->get_attribute_value('class') == '2a') {
// 			$tagr->set_attribute('happy', 'days');
// 		}
// 	}
// }
// 
// 
// 
// echo $tag->display('pretty');


foreach($class as $class_tags) {
	//var_dump($tag);
	foreach($class_tags as $tagr) {
		if( $tagr->get_attribute_value('class') == '2a') {
			$tagr->_delete();
		}
	}
}


echo $tag->display('pretty');
