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
		$output .= "<br>" . PHP_EOL;
		$format = "&lt;?php echo get_post_meta(\$post->ID, '%s', true);?&gt;";
		foreach ( $metadata as $key => $value ) {
			$output .= sprintf($format,$value['meta_key']);
			$output .= "<br>" . PHP_EOL;
		}
		return $output;

	}
}
class Acftab extends Tabdata {
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
			$before .= " if (" . implode($join,$if) . ") { <br>" . PHP_EOL;
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
		$output .= "<hr>";
		$output .= __('Please save the post before you paste these codes.','custom-field-snippet');
		return $output;
	}
	function output_field($fields,$sub=false) {
		static $output;
		if ($sub) {
			$format = " get_sub_field('%s')";
		} else {
			$format = " get_field('%s')";
		}
		$formatecho = "echo " . $format . ";";
		$formatif = "if (" . $format . ") {";
		$formatsubwhile = " while(has_sub_field('%s')) {";
		foreach ( $fields as $field ) {
			$before = "&lt;?php <br>" . PHP_EOL;
			$after = "?&gt; <br>" . PHP_EOL;
			$this->cfs_add_conditional($fields,$field,$before,$after);
			$output .= $before;
			if ($field['type'] == 'repeater') {
				$output .= sprintf($formatif,$field['name']);
				$output .= "<br>" . PHP_EOL;
				$output .= sprintf($formatsubwhile,$field['name']);
				$output .= "<br>" . PHP_EOL;
				$output .= $after ;
				$output .= $this->output_field($field['sub_fields'],true);
				$output .= $before;
				$output .= "} // ";
				$output .= sprintf($formatsubwhile,$field['name']);
				$output .= "<br>" . PHP_EOL;
				$output .= "} // ";
				$output .= sprintf($formatif,$field['name']);
			} else {
				$output .= sprintf($formatecho,$field['name']);
			}
			$output .= "<br>" . PHP_EOL;
			$output .= $after ;
		}
		return $output;
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
