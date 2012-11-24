<?php 

abstract class Tabdata {
	protected $name;
	protected $label;
	abstract public function getdata();
	final public function getname() {
		if ('' != $this->name) {
			return $this->name;
		} else {
			return get_called_class();
		}
	}
	final public function getlabel() {
		if ('' != $this->label) {
			return $this->label;
		} else {
			return $this->getname();
		}
	}
}

class Defaulttab extends Tabdata {
	function __construct() {
		$this->name = 'Default';
		$this->label = __('Default','custom-field-snippet');
	}
	function getdata() {
	global $post;
		// fork from meta-box.php post_custom_meta_box
		$metadata = has_meta($post->ID);
		foreach ( $metadata as $key => $value ) {
			if ( is_protected_meta( $metadata[ $key ][ 'meta_key' ], 'post' ) || ! current_user_can( 'edit_post_meta', $post->ID, $metadata[ $key ][ 'meta_key' ] ) )
				unset( $metadata[ $key ] );
		}
		$output = "&lt;?php \$post->ID = $post->ID;?&gt;";
		$output .= PHP_EOL;
		$format = "&lt;?php echo get_post_meta(\$post->ID, '%s', true);?&gt;";
		foreach ( $metadata as $key => $value ) {
			$output .= sprintf($format,$value['meta_key']);
			$output .= PHP_EOL;
		}
		return $output;

	}
}
class Acftab extends Tabdata {
	protected $output;
	function __construct() {
		$this->name = 'Acf';
		$this->label = __('Acf','custom-field-snippet');
	}
	// fork from Advanced Custom Fields plugin: acf.php create_field
	// Thank you, Elliot. 
	function cfs_add_conditional($fields,$field,&$before,&$after) {
		if( isset($field['conditional_logic']) && $field['conditional_logic']['status'] == '1' ) {
			$join = ' && ';
			if( $field['conditional_logic']['allorany'] == "any" )
			{
				$join = ' || ';
			}
			foreach( $field['conditional_logic']['rules'] as $rule )
			{
				$field_name = $this->get_field_name_from_key($fields,$rule['field']);
				$if[] = 'get_field("' . $field_name . '") ' . $rule['operator'] . ' "' . $rule['value'] . '"' ;
			}
			$before .= " if (" . implode($join,$if) . ") { ";
			$after = "} " . $after;
		}
	}
	function getdata() {
		global $acf;
		global $post;
		$boxes = $acf->get_input_metabox_ids(array('post_id' => $post->ID), false);
		$output = '';
		foreach ( $boxes as $box) {
			$fields = $acf->get_acf_fields($box);
			$output .= $this->output_field($fields);
		}
		return $output;
	}
	function output_field($fields,$sub=false) {
		if ($sub) {
			$format = " get_sub_field('%s')";
		} else {
			$format = " get_field('%s')";
		}
		$formatecho = "echo " . $format . ";";
		$formatif = "if (" . $format . ") {";
		$formatsubwhile = " while(has_sub_field('%s')) {";
		foreach ( $fields as $field ) {
			$before = "&lt;?php ";
			$after = "?&gt; ";
			$this->cfs_add_conditional($fields,$field,$before,$after);
			$this->output .= $before;
			$this->output .= PHP_EOL;
			if ($field['type'] == 'repeater') {
				$this->output .= sprintf($formatif,$field['name']);
				$this->output .= PHP_EOL;
				$this->output .= sprintf($formatsubwhile,$field['name']);
				$this->output .= PHP_EOL;
				$this->output .= $after ;
				$this->output .= PHP_EOL;
				$this->output_field($field['sub_fields'],true);
				$this->output .= $before;
				$this->output .= PHP_EOL;
				$this->output .= "} // ";
				$this->output .= sprintf($formatsubwhile,$field['name']);
				$this->output .= PHP_EOL;
				$this->output .= "} // ";
				$this->output .= sprintf($formatif,$field['name']);
			} else {
				$this->output .= sprintf($formatecho,$field['name']);
			}
			$this->output .= PHP_EOL;
			$this->output .= $after ;
			$this->output .= PHP_EOL;
		}
		return $this->output;
	}
	function get_field_name_from_key($fields,$fieldname) {
		foreach ($fields as $field2) {
			if ($fieldname == $field2['key']){
				return $field2['name'];
			}
		}
		return $fieldname;
	}
}
class Acfshortcode extends Tabdata {
	protected $output;
	function __construct() {
		$this->name = 'Acfsc';
		$this->label = __('Acf Short Code','custom-field-snippet');
	}
	function getdata() {
		global $acf;
		global $post;
		$boxes = $acf->get_input_metabox_ids(array('post_id' => $post->ID), false);
		$output = '';
		foreach ( $boxes as $box) {
			$fields = $acf->get_acf_fields($box);
			$output .= $this->output_field($fields,$post->ID);
		}
		return $output;
	}
	function output_field($fields,$postid='') {
		if ('' == $postid){
			global $post;
			$postid = $post->ID;
		}
		$format = '[acf field="%s" post_id="%d"]';
		foreach ( $fields as $field ) {
			$this->output .= sprintf($format,$field['name'],$postid);
			$this->output .= PHP_EOL;
		}
		return $this->output;
	}
}
