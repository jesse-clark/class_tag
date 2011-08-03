<?php
/**
    A demonstration for class tag extensions, this one is for html5.

    @copyright Jesse Clark 2010-2011

    @see http://html5boilerplate.com/
    @todo Finish adding html5 support, ie.. new constructor
    @todo Finish building out the html5 boilerplate support.
 */
require_once('../class_tag.php');

class tag_html5 extends tag
{
    /**
     * Object pointer to the head section.
     */
    public $head;

    /**
     * Object pointer to the body section.
     */
    public $body;

    /**
     * Object pointer to the title section
     */
    public $title;

    /**
     * This function generates the html5 boilerplate spec into a class tag layout.
     *
     * @return
     *   Object pointing to the body section.
     *
     * @todo Finish building out the html5 boilerplate.
     */
    public function full_html5() {
        $html = new tag_html5(); //create an empty class as a container
        $html   ->add_text('<!doctype html>')->_el()//have to add el to pretty print
                ->add_comment('paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/')->_el()
                ->add_comment('[if lt IE 7]> <html class="no-js ie6" lang="en"> <![endif]')->_el()
                ->add_comment('[if IE 7]>    <html class="no-js ie7" lang="en"> <![endif]')->_el()
                ->add_comment('[if IE 8]>    <html class="no-js ie8" lang="en"> <![endif]')->_el()
                ->add_comment('[if gt IE 8]><!-->  <html class="no-js" lang="en"> <!--<![endif]');
         
        $this->head = $html->head();//creates a section and saves it as a pointer
        $this->body = $html->body();
        $html->_el()->add_text('</html>'); //We do this due to the nonstandard html start tag

        $this->title = $this->head  ->meta('charset','utf-8')->_p()//link back to head
	 		                        ->meta('http-equiv', 'X-UA-Compatible', 'content', 'IE=edge,chrome=1')->_p()
			                        ->title();
		$this->add_text($html);
		return $this->body;
    }

    /**
     * Adds a new tag to the current tag.
     *
     * Reimplemented version of add_tag done so that new tags become tag_html5 type.
     *
     * @param $_name
     *   Name of new tag.
     *
     * @return
     *   New class tag_html5 object.
     */
    public function add_tag($_name) {
		if($this->void_element) {
			throw new Exception('Added element to an HTML5 Void Element');
		}
		$tag = new tag_html5($_name, $this);//tag_html5 here
		$this->children[] = $tag;
		return $tag;
	}  

    /**
     * Builds a dropdown (select box) from an array.
     *
     * @param $array
     *   Array to build drop down menu from.
     *
     * @param $selected
     *   String - optional, if found that item in the dropdown is selected.
     *
     * @return
     *   Object tag_html5 pointer to the added dropdown.
     */
    public function _dropdown($array,$selected = '') {
        $dropdown = new tag_html5('select');
        $selected_state = !empty($selected);//keep from cheking this in every loop pass
        foreach($array as $value) {
            $option = $dropdown->option('value',$value)->add_text($value);
            if($selected_state && $value == $selected) {
                $option->set_attribute('selected');
            }
        }
        $this->add_text($dropdown);
        return $dropdown;
    }

    /**
     * Method to add endlines when pretty printing.
     *
     * This function will add an object, that will print a single endline if 
     * in pretty print mode. This was needed so that when strings are placed 
     * in tags you could endline after them because pretty print mode doesn't
     * endline after string placement.
     *
     * @return
     *   $this
     *
     * @see full_html5()
     * @see class tag_endline
     */
    private function _el() {
        $endline = new tag_endline();
        $this->children[] = $endline;
        return $this;
    }
}

/**
    A simple class used to return an endline "\n" when in pretty print mode.
*/
class tag_endline 
{
    /**
     * Returns an endline "\n" in pretty print mode.
     *
     * @return
     *   String, "\n"
     */
    public function display($option) {
        if($option == 'pretty')
            return PHP_EOL;
    }
}
