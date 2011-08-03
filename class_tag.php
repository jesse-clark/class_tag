<?php
/**
    The following class is used to generate a markup language.

    @copyright Jesse Clark 2010-2011  

    @todo Revmove remaining html5 structure and move it to the html5 class.
    @todo Evaluate which class tag members need to be changed to protected.
*/

class tag {
    
    /**
     * String, the name of the tag.
     */ 
    private $name;

    /**
     * Array, Holds the tag attributes where  key=>value is key="value" or 
     *   "key" (when no value)
     */
	private $attributes;

    /**
     * Boolean, determines if the tag is a void element, and is self closing.
     *
     * @see tag()
     */
    protected $void_element;

    /**
     * Pointer to the parent class or self if no parent.
     */
    private $parent;

    /**
     * Array, contains class tag type classes, these will apear inside $this tag.
     */
    protected $children;

    /**
     * Array, contaings liks to all attributes (including all tag child classes 
     *   thanks to the statc). Originally developed to keep track of all html 
     *   attributes named class.
     *
     * @see set_attribute()
     * @see search_attributes()
     */
    private static $attribute_index;

    /**
     * Class tag constructor.
     *
     * @param $_name
     *   The name of the tag.
     *
     * @param $_parent
     *   Optional, takes a class and sets it as $this->parent. Not usually used by
     *     an end user.
     */
	public function tag($_name = '',$_parent = false) {
        //If there is no parent, the parent links to self, set below.
        if($_parent === false) {
			$_parent = $this;
		}

        //html5 void elements
		$void_elements =  array(  'area', 'base', 'br', 'col', 'command',
								  'embed', 'hr', 'img', 'input', 'keygen',
								  'link', 'meta', 'param', 'source', 'track', 'wbr'
							   );
		
        //Check to see if the tag is a void tag.
        //  Void tags are self closing, ie.. <br />
		if(in_array($_name, $void_elements)) {
			$this->void_element = true;
		} else {
			$this->void_element = false;
		}

        //set the class variables
		$this->name 		= $_name;
		$this->attributes 	= array();
		$this->parent 		= $_parent;
		$this->children 	= array();
	}

    /**
     * Magic Method 
     *
     *   If class member function call isn't defined it is assumed to
     *   be an html tag name. This allows the user to call:
     *   $tag->head() instead of $tag->add_tag('head')
     *
     * @return
     *   new class tag object
     *
     * @see http://www.php.net/manual/en/language.oop5.overloading.php
     */
	public function __call($name, $arguments = array()) {
		$tag = $this->add_tag($name);
		$tag->set_attributes($arguments);
		return $tag;
	}

    /**
     * Adds a new tag to the current tag.
     *
     * @param $_name
     *   Name of new tag.
     *
     * @return
     *   new class tag object
     */
	public function add_tag($_name) {
        //Check to see current tag is void, if so throw an error
        // ie.. you can't add a child tag to <br />
        if($this->void_element) {
			throw new Exception('Added element to an HTML5 Void Element');
		}
		$tag = new tag($_name, $this);
		$this->children[] = $tag;
		return $tag;
	}

    /**
     * Adds text to the class tag children.
     *
     * @param $_text
     *   String, to be added to the children.
     *
     * @return
     *   $this
     */
	public function add_text($_text) {
		$this->children[] = $_text;
		return $this;
	}

    /**
     * Adds a comment to the class tag children.
     *
     * @param $_text
     *   String, to be added as a comment to the children.
     *
     * @return
     *   $this
     */
	public function add_comment($_text) {
		$this->add_text('<!--' . $_text . '-->');
		return $this;
	}

    /**
     * Sets a single attribute to the current tag.
     *
     *   ie.. &lt;a $_key="$_value"&gt;
     *
     * @param $_key
     *   A tag attribute.
     *
     * @param $_value
     *   A tag attribute paramater.
     *
     * @return
     *   $this
     */
	public function set_attribute($_key,$_value = '') {
        //Commented this out so that all attributes are saved
        // if($_key = 'class') {
			self::$attribute_index[$_key][$_value][] = $this;
		// }
		$this->attributes[$_key] = $_value;
		return $this;
	}

    /**
     * Deletes a given class tag.
     *
     * @see delete_child()
     */
	public function _delete() {
		$this->parent->delete_child($this);
	}

    /**
     * Deletes a child from a parent.
     *
     * @param $child_to_delete
     *   Object or string to delete from children.
     * 
     * @return
     *   $this
     */
	private function delete_child($child_to_delete) {
		foreach($this->children as $key => $child) {
			if($child === $child_to_delete) {
				unset($this->children[$key]);
			}
		}
		return $this;
	}

    /**
     * Return a list of class tags that have specified attribute.
     *
     * @param $_attribute
     *   String, attribute your looking for
     *
     * @return
     *   Array of Class tag object pointers
     */
	public function search_attributes($_attribute) {
		return self::$attribute_index[$_attribute];
	}

    /**
     * Returns the value of the specified attribute from the current tag.
     *
     * @param $_attribute
     *   String, the attribute your searching.
     *
     * @return
     *   String or False, string if attribute is found.
     */
	public function get_attribute_value($_attribute) {
		if(isset($this->attributes[(string) $_attribute])) {
			return $this->attributes[(string) $_attribute];
		}
		return false;
	}

    /**
     * Sets the attributes of a tag from an array
     *
     * @param $attributes
     *   Array of attributes $key= Attribute, $value = attribute paramater
     *   -OR-
     *   Array of attributes with attributes in even keys and attributes in odd keys
     *
     * @return
     *   $this
     *
     * @see set_attribute()
     */
	public function set_attributes($attributes = array() ) {
        $count = count($attributes);
		if( $count == 0) {
			return $this;
		}
        end($attributes); //point to the last key in the $attribute array

        //if the last key in $attributes is a number and is one less than the size 
        //of $attriutes (becuase array keys start at 0) then all of our attributes 
        //are in the value side of the $attirbutes array ($key=>$value)
        if( (key($attributes) + 1)  == $count ) {
            //if we have an odd number of values add an empty value
            if( ($count % 2) == 1) {
				$attributes[] = '';
			}
			$i = 1;
			$odd_value = ''; //this is even value of the array, see below foreach
            
            //This foreach saves the $value on odd passes, and 
            //runs set_attribute on even passes
            foreach( $attributes as $value ) {
                if( $i % 2 == 1) {
					$odd_value = $value;
				} else {
					$this->set_attribute($odd_value, $value);
				}
				$i++;
			}
        } else {
            //We were passed a correctly formed array ($attribute=>$param)
			foreach( $attributes as $key => $value ) {
				$this->set_attribute($key,$value);
			}
		}
		return $this;
	}

    /**
     * Returns the parent of $this, allows for backtracking when chaning functions.
     *
     * @return
     *   $this->parent
     */
	public function parent() {
		return $this->parent;
	}

    /**
     * An Alias for parent().
     *
     * @see parent()
     */
	public function _p() {
		return $this->parent();
	}

    /**
     * Returns this tag and children as a well formed string.
     *
     * @param $option
     *   String, Optiona 'pretty' adds spacing to make the tags human readable.
     *
     * @return
     *   String, Returns the tags as a string.
     */
	public function display($option = 'min') {
		switch ($option) {
			case 'pretty':
        		return $this->display_pretty($option);
			default:
				return $this->display_minimized($option);
		}
	}

    /**
     * Prepares tag and children as a computer string. (no whitespace)
     *
     * @param $option
     *   Used when calling the public display function on children.
     *
     * @return
     *  String of tags formatted for computer use.
     *
     * @see display()
     * @see prepare_tag()
     */
    private function display_minimized($option) {
    	$display = '';
		$tag = $this->prepare_tag(); //returns an array [0]-start [1]-end/empty
		if(!empty($this->children)) {
			foreach($this->children as $child_tag) {
                //sometimes a child is just some text, ie.. comments, text
                if(is_string($child_tag)) {
					$display .= $child_tag;
				} else {
					$display .= $child_tag->display($option);
				}
			}
		}
		return $tag[0] . $display . $tag[1];
	}


    /**
     * Prepares tag and children as a human readable string. (whitespace)
     *
     * This is the longer explaination of function.
     *
     * @param $option
     *   Used when calling the public display function on children.
     *
     * @return
     *   String of tags formatted with whitespace for human readablility.
     *
     * @see display()
     * @see prepare_tag()
     */
    private function display_pretty($option = 'pretty') {
        //We don't want to pass the tab amount around with this recursive function.
        //So a static is used to keep track of the white space, or tab amount for
        //that leve.
        static $tab_amount;
        $tab = '  '; //set the kind of tab to be used, I prefer '  ' to "\t"
		if(!isset($tab_amount)) {
			$tab_amount = '';
		} elseif(!empty($this->name)) {
			$tab_amount .= $tab;
		}
        $display = '';
        $tag = $this->prepare_tag();//get the start and end tags
        $count = 0;//used to count the number of children that aren't strings
        $recieved_endline = false;//did we add an "\n"
		if(!empty($this->children)) {//end condition for recursion
            foreach($this->children as $child_tag) {
				if(is_string($child_tag)) {
					$display .= $child_tag;//will continue to next element
                } else {
                    $to_display =  $child_tag->display($option);
                    if(empty($to_display)) {//sometimes this happens
                        continue;           //no foul if it does
                    }
                    //order of precedence && then ||
                    if(!empty($tag[0]) || $count > 0 && $to_display != PHP_EOL) {
                        $to_display = PHP_EOL . $tab_amount . $to_display;
                        $recieved_endline = true;
                    }
                    $display .= $to_display;
                    $count++;
				}
			}
        }
        //reset the tab amount to what it is for this level of tab
        $tab_amount = substr($tab_amount, 0, -(strlen($tab)));

        //if we added tags, we are in a tag, and we used tabs for children
        if($count > 0 && !empty($tag[0]) && !$recieved_endline) {
            $tag[0] = PHP_EOL . $tab_amount . $tag[0];
        }

        //if we added non text tabs
        if($count > 0) {
            $tag[1] = PHP_EOL . $tab_amount . $tag[1];
        }
		return $tag[0] . $display  . $tag[1]; 
    }

    /**
     * Prepares the open and close tags from $this->name..
     *
     * @return
     *   Array containing:
     *   - [0]: Open tag, void tag, or empty string
     *   - [1]: Close tag or empty string
     */
	private function prepare_tag() {
		$return_array = array(0 =>'', 1=>'');//initialize return array
		if(empty($this->name)) {
			return $return_array;
		}
        //creating opening tag
        $return_array[0] = '<' . $this->name;
        //add attributes
        if(!empty($this->attributes)) {
			foreach($this->attributes as $key=>$value) {
				$return_array[0] .= ' ' . $key;
				if(!empty($value)) {
					$return_array[0] .= '="' . $value . '"';
				}
			}
        }

        //close opening tag, and possibly create closing tag
		if($this->void_element) {
			$return_array[0] .= ' />';
		}else{
			$return_array[0] .= '>';
			$return_array[1] .= '</' . $this->name . '>';
		}
		return $return_array;
	}


}
