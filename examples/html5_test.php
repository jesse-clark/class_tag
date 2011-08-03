<?php

require_once('class_html5.php');

//Load the class
$html = new tag_html5();

//Load the html5 boilerplate structure.
$html->full_html5();  

//We will refrence body a lot, so lets make it a variable
$body = &$html->body;


//Set the title.
$html->title->add_text('Test Site');

//add a paragraph, text, anchor with text, then go back to the paragragh and add text
$body->p()
        ->add_text('Welcome ')
            ->a('href','http://localhost/~jesse/tag/html5_test.php')
                ->add_text('to my html5 test page')
            ->_p()//the _p means to go to the parent, in this case the p tag
        ->add_text('. Thanks for visiting. Please read the php source for an overview of what is going on.');

//lets add a form and save a pointer to it
$form = $body->form('method','post');

//In the form we will add 4 radio buttons
for($i = 1; $i < 5; $i++) {
    $input = $form->input(  'name','input',
                            'type','radio',
                            'value','input_'.$i
                         );
    //here we are seeing if any of the radio buttons were set, 
    //since they were a group only one can be set
    if(!empty($_POST['input']) && $_POST['input'] == 'input_'.$i) {
        $input->set_attribute('checked');
    }
}
//a regular submit button
$form->input('type','submit','name','submit','value','regular submit');

//this submit button will trigger, the following if statement.
$form->input('type','submit',
             'name','submit_1',
             'value','submit (change to checkboxes)');

//This if statement will change the radio button to checkboxes.
//This is to demonstrate how to use the search_attributes feature.
//Normally I wouldn't condone such kind of change, but it could have a place:
//  ie.. Instead of a button, the page detects that a privileged user is 
//       running who can set multiple options. 
if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['submit_1'])) {
    //pull the 'type' attributes
    $attributes = $html->search_attributes('type');
    $i = 1;
    //only look at types with paramater radio
    foreach($attributes['radio'] as $change_me) {
        $input_name = 'input_' . $i;
        //change the type and name 
        $change_me->set_attributes(array('type'=>'checkbox','name'=>$input_name));
        if(!empty($_POST[$input_name])) {
            $change_me->set_attribute('checked');
        }
        $i++;
    }
}

//a demo of loading a drop down menu from an array
//Think of loading a column from a database (in our case no db, just an array)
$column_from_db = array();
date_default_timezone_set('UTC');
for($i = 0; $i <10; $i++) {
    $column_from_db[] = date('Y-m-d', strtotime("-$i week"));//fill array with dates
}

//first the standard column
$body->_dropdown($column_from_db);
$body->br();

//Then a column with a date selected
$body->_dropdown($column_from_db, date('Y-m-d', strtotime('-4 week')));
$body->br();

//Finally a drop down with an extra option added and selected
$body->_dropdown($column_from_db)
     ->option('value','','selected')
     ->add_text('Other...');



//The following adds a button to display the non-human frindly display.
$form = $body->br()->_p()->br()->_p()->form('method','post');
$form->add_text('Please view the source before and after pressing this button:');
$form->input('type','submit',
             'name','submit_2',
             'value','Show Minimized HTML Source');  

//Echo the display to the page
if(!empty($_POST['submit_2'])) {
    //This would be a normal display method.
    echo $html->display();
} else {
    //This is the pretty print display method.
    echo $html->display('pretty');
}

