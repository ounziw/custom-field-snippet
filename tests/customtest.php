<?php
/*
 * GPL ver.2
 * GCopyright 2012 by Fumito MIZUNO http://php-web.net/
 * Ghttp://www.gnu.org/licenses/gpl-2.0.html
 */

class CustomTest extends PHPUnit_Framework_TestCase {
	protected $obj;
	protected $field = array(
		array('key'=>'key1','name'=>'name1'),
		array('key'=>'key2','name'=>'name2'),
	);
	public function setUp() {
	    $this->obj = new Acftab;
	}
	public function test_add_conditional() {
		$input = array(
			'conditional_logic' => array(
				'status' => 1,
				'allorany' => 'any',
				'rules' => array(
					array(
						'field' => 'field1',
						'operator' => '==',
						'value' => '1',
					),
					array(
						'field' => 'field2',
						'operator' => '!=',
						'value' => 'abc',
					),
				),
			),
		);
		$before = "&lt;?php";
		$after = "?&gt;";
		$this->obj->cfs_add_conditional($this->field,$input,$before,$after);
		$expected = <<<EOF
&lt;?php if (get_field("field1") == "1" || get_field("field2") != "abc") { 
EOF;
		$expected2 = "} ?&gt;";
		$this->assertEquals($expected, $before);
		$this->assertEquals($expected2, $after);
	}
	public function test_get_field_name_from_key() {
		$input = 'key2';
		$output = $this->obj->get_field_name_from_key($this->field,$input);
		$expected = 'name2';
		$this->assertEquals($expected, $output);
	}
	public function test_outputfield() {
		$field = array(
			array('type'=>'text','name'=>'name1'),
			array('type'=>'text','name'=>'name2'),
		);	
		$output = $this->obj->output_field($field,false);
		$expected = <<<EOF
&lt;?php 
echo esc_html( get_field('name1'));
?&gt; 
&lt;?php 
echo esc_html( get_field('name2'));
?&gt; 

EOF;
		$this->assertEquals($expected, $output);
	}
}

