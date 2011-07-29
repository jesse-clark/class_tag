<?php
/**
	\file class_tag.php
	The following class is used to keep track of tags.
*/

//!namespace Tags_by_Jesse;

class tag {

	private $name;			//string
	private $attributes;    //array : key=>value = "key = value" or "key" (when no value)
	private $void_element;  //boolean	
	private $parent;        //pointer
    protected $children;		//array of class tag

	private static $class;

	public function tag($_name = '',$_parent = false) {
		if($_parent === false) {
			$_parent = $this;
		}

		$void_elements =  array(  'area', 'base', 'br', 'col', 'command', 
								  'embed', 'hr', 'img', 'input', 'keygen', 
								  'link', 'meta', 'param', 'source', 'track', 'wbr'
							   );                       
		
		
		if(in_array($_name, $void_elements)) {
			$this->void_element = true;
		} else {
			$this->void_element = false;
		}

		$this->name 		= $_name;
		$this->attributes 	= array();
		$this->parent 		= $_parent;
		$this->children 	= array();
	}

	public function __call($name, $arguments = array()) {
		$tag = $this->add_tag($name);
		$tag->set_attributes($arguments);
		return $tag;
	}

	public function add_tag($_name) {
		if($this->void_element) {
			throw new Exception('Added element to an HTML5 Void Element');
		}
		$tag = new tag($_name, $this);
		$this->children[] = $tag;
		return $tag;
	}

	public function add_text($_text) {
		$this->children[] = $_text;
		return $this;
	}

	public function add_comment($_text) {
		$this->add_text('<!--' . $_text . '-->');
		return $this;
	}

	public function set_attribute($_key,$_value = '') {
		// if($_key = 'class') {
			self::$class[$_key][$_value][] = $this;
		// }
		$this->attributes[$_key] = $_value;
		return $this;
	}

	public function _delete() {
		$this->parent->delete_child($this);
	}

	private function delete_child($child_to_delete) {
		foreach($this->children as $key => $child) {
			if($child === $child_to_delete) {
				unset($this->children[$key]);
			}
		}
		return $this;
	}

	public function search_attributes($_attribute) {
		return self::$class[$_attribute];
	}

	public function get_attribute_value($_attribute) {
		if(isset($this->attributes[(string) $_attribute])) {
			return $this->attributes[(string) $_attribute];
		}
		return false;
	}

	public function set_attributes($attributes = array() ) {
		$count = count($attributes);
		if( $count == 0) {
			return $this;
		}
		end($attributes);
		if( (key($attributes) + 1)  == $count ) {
			if( ($count % 2) == 1) {
				$attributes[] = '';
			}
			$i = 1;
			$odd_value = '';
			foreach( $attributes as $value ) {
            	if( $i % 2 == 1) {
					$odd_value = $value;
				} else {
					$this->set_attribute($odd_value, $value);
				}
				$i++;
			}
		} else {
			foreach( $attributes as $key => $value ) {
				$this->set_attribute($key,$value);
			}
		}
		return $this;
	}

	public function parent() {
		return $this->parent;
	}

	public function _p() {
		return $this->parent();
	}

	public function display($option = 'min') {
		switch ($option) {
			case 'pretty':
        		return $this->display_pretty($option);
			default:
				return $this->display_minimized($option);
		}
	}

    private function display_minimized($option) {
    	$display = '';
		// if(!empty($this->name)) {
		// 	$display .= '<' . $this->name;
		// 	if(!empty($this->attributes)) {
		// 		foreach($this->attributes as $key=>$value) {
		// 			$display .= ' ' . $key;
		// 			if(!empty($value)) {
		// 				$display .= '="' . $value . '"';
		// 			}
		// 		}
		// 	}
		// 	
		// 	if($this->void_element) {
		// 		$display .= ' />';
		// 		return $display;
		// 	} else {
		// 		$display .= '>';
		// 	}
        //}
		$tag = $this->prepare_tag();
		if(!empty($this->children)) {
			foreach($this->children as $child_tag) {
				if(is_string($child_tag)) {
					$display .= $child_tag;
				} else {
					$display .= $child_tag->display($option);
				}
			}
		}
        //if(!empty($this->name)) {
		//	$display .= '</' . $this->name . '>';
		//}
		return $tag[0] . $display . $tag[1];
	}



    private function display_pretty($option = 'pretty') {
        static $tab_amount;
        $tab = '  ';
		if(!isset($tab_amount)) {
			$tab_amount = '';
		} elseif(!empty($this->name)) {
			$tab_amount .= $tab;
		} 
        $display = '';
        $tag = $this->prepare_tag();
        $count = 0;
        $recieved_endline = false;
		if(!empty($this->children)) {
            foreach($this->children as $child_tag) {
				if(is_string($child_tag)) {
					$display .= $child_tag;
                } else {
                    $to_display =  $child_tag->display($option);
                    if(empty($to_display)) {
                        continue;
                    }
                    if(!empty($tag[0]) || $count > 0 && $to_display != "\n") {
                        $to_display = "\n" . $tab_amount . $to_display;
                        $recieved_endline = true;

                    }
                    if(!empty($tag[1])) {
                        //$to_display .= "\n";
                    }
                    $display .= $to_display ;
                    $count++;
				}
			}
        }
        $tab_amount = substr($tab_amount, 0, -(strlen($tab)));
        if($count > 0 && !empty($tag[0]) && !$recieved_endline) {
            $tag[0] = "\n" . $tab_amount . $tag[0];
        }
        if(!empty($display)) {
            //$display .= "\n";
        }
        if($count > 0) {
            $tag[1] = "\n" . $tab_amount . $tag[1];
        }
		return $tag[0] . $display  . $tag[1]; 
    }

    private function display_pretty3($option = 'pretty') {
        $display = '';
        $tag = $this->prepare_tag();
        $count = 0;
        foreach($this->children as $child_tag) {
            
            if(is_string($child_tag)) {
                $display .= $child_tag;
            } else {
                $display .=  $child_tag->display($option);
            }
            $count++;
        }
        if(!empty($tag[0])) {
            if(!empty($tag[1])) {
            }
        }    
        return $tag[0]  . $display  . $tag[1];
    }


    private function display_pretty4($option = 'pretty') {
        static $tab_amount;
        $tab = '  ';
		if(!isset($tab_amount)) {
			$tab_amount = '';
		} elseif(!empty($this->name)) {
			$tab_amount .= $tab;
		}


		$display = '';
		$tag = $this->prepare_tag(/*$tab_amount*/);

        static $should_i_endline = false;
        //$osie = $should_i_endline;
		$counter = 0;
		if(!empty($this->children)) {
			$child_count = count($this->children);
            foreach($this->children as $child_tag) {
                $child_tag_is_string = is_string($child_tag);
                if($child_tag_is_string) {
                    $display .= $child_tag;
                } else {
                    $child_tag_data = $child_tag->display($option);
                    if((!empty($this->name) || $counter >0) && ($should_i_endline || $child_count > 1)) {
                        //$should_i_endline = false;
                        $display .= "\n" . $tab_amount;
                        $counter++;
                    }
                    $display .= $child_tag_data;

                }
 //   			$child_tag_is_string = is_string($child_tag);
 //   			if(!$child_tag_is_string) {
 //   				$child_tag_data = $child_tag->display($option);
 //   			}
 //   			if(!empty($this->name) && $should_i_endline || $child_count > 1) {
 //   				$display .= "\n" . $tab_amount;
 //   			}
 //   			if($child_tag_is_string) {
 //   				$display .= $child_tag;
 //   			} else {
 //   				$display .= $child_tag_data;
 //   			}
 //   			$counter++;
			

//				if(is_string($child_tag)) {
//					$display .= $child_tag;
//				} else {
//					$child_tag_data = $child_tag->display($option);
//					if(!empty($this->name) && $should_i_endline || $child_count > 1) {
//					    $display .= "\n" . $tab_amount;
//					} else {
//						//$tab_amount = substr($tab_amount, 0, -strlen($tab));
//					}
//					
//
 //   				//if( ($end_line_counter > 0) || ($child_count > 1) ) {     
 //   					//var_dump($tab_amount);
 //   					//$display .= $tab_amount;
 //   					$counter++;
  //  				//}
   // 				$display .= $child_tag_data;
	//				//$should_i_endline = true;
	 //   		}
			}
        }
        $tab_amount = substr($tab_amount, 0, -(strlen($tab))); 
        if($counter > 1) {
            //$display .= "\n" . $tab_amount;
			$should_i_endline = true;
        }
        //if($counter > 1)
          //  $display .= "\n" . $tab_amount;
        if($should_i_endline /*&& !empty($this->name)*/) {
            $display .= "\n" . $tab_amount;
            //$should_i_endline = false;
        }
        //$should_i_endline = $osie;
		return $tag[0] . $display . $tag[1] . '<!-- ' . $counter . ' ' . (string) $should_i_endline . ' -->';


	}

	private function prepare_tag($tab_over_amount = '') {
		$return_array = array(0 =>'', 1=>'');
		if(empty($this->name)) {
			return $return_array;
		}
        $return_array[0] = $tab_over_amount . '<' . $this->name;
		if(!empty($this->attributes)) {
			foreach($this->attributes as $key=>$value) {
				$return_array[0] .= ' ' . $key;
				if(!empty($value)) {
					$return_array[0] .= '="' . $value . '"';
				}
			}
		}
		if($this->void_element) {
			$return_array[0] .= ' />';
		}else{
			$return_array[0] .= '>';
			$return_array[1] .= $tab_over_amount . '</' . $this->name . '>';
		}
		return $return_array;
	}


}
