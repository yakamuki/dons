<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Posts Meta Group Class.
 *
 * @package crane
 */


class Crane_Meta_Data_Group {
	protected $name;
	protected $title;
	protected $postType;
	protected $context;
	protected $priority;
	protected $fields;

	/**
	 * @param $name
	 * @param $title
	 * @param $postType
	 * @param $priority
	 * @param $context
	 * @param $fields
	 */
	public function __construct( $name, $title, $postType, $fields , $priority = 'high', $context = 'normal') {
		$this->context  = $context;
		$this->fields   = $fields;
		$this->name     = $name;
		$this->postType = $postType;
		$this->priority = $priority;
		$this->title    = $title;
	}

	/**
	 * @return mixed
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * @return Crane_Meta_Data_Field[]
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return mixed
	 */
	public function getPostType() {
		return $this->postType;
	}

	/**
	 * @return mixed
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * @return mixed
	 */
	public function getTitle() {
		return $this->title;
	}

	public function show($post) {
		?>
		<div class="grooni-metabox">
		<?php
		foreach($this->getFields() as $field) {
			echo empty( $post->ID ) ? '' : $field->render( $post->ID );
		}
		?>
		</div>
		<?php
	}

} 