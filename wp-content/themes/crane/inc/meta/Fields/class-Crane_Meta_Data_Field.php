<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Field constructor for Posts Meta.
 *
 * @package crane
 */


class Crane_Meta_Data_Field {
	static $values = [ ];
	protected $name;
	protected $title;
	protected $default;
	protected $conditions;
	protected $description;
	protected $isIndependentMeta;
	protected $inputAttr = array();
	protected $postType = array();

	/**
	 * @param $name
	 * @param $title
	 * @param $default
	 */
	public function __construct( $name, $title, $default ) {
		$this->default = $default;
		$this->name    = $name;
		$this->title   = $title;
	}

	/**
	 * @param $postType
	 *
	 * @return $this
	 */
	public function setPostType( $postType ) {
		if ( ! is_array( $postType ) ) {
			$postType = array( $postType );
		}
		$this->postType = $postType;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getPostType() {
		return $this->postType;
	}

	/**
	 * @param $inputAttr
	 *
	 * @return $this
	 */
	public function setInputAttr( $inputAttr ) {
		if ( ! is_array( $inputAttr ) ) {
			$inputAttr = array( 'data-'. $inputAttr => $inputAttr );
		}
		$this->inputAttr = $inputAttr;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getInputAttr() {
		return $this->inputAttr;
	}

	/**
	 * @return mixed
	 */
	public function getConditions() {
		return $this->conditions;
	}

	public function setConditions( $conditions ) {
		$this->conditions = $conditions;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDescription() {
		return $this->description;
	}

	public function setDescription( $description ) {
		$this->description = $description;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDefault() {
		return $this->default;
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
	public function getTitle() {
		return $this->title;
	}


	public function SetIndependentMeta( $value ) {
		$this->isIndependentMeta = (bool) $value;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isIndependentMeta() {
		return $this->isIndependentMeta;
	}


	public function render( $post_id ) {
		$conditions = '';
		if ( $this->getConditions() != null ) {
			$conditions = 'data-condition=\'' . json_encode( $this->getConditions() ) . '\'';
		}

		return
			'<div ' . $conditions . ' class="grooni-metabox-row">
				<div class="grooni-metabox-title">
					' . $this->getTitle() . '
					<p>' . $this->getDescription() . '</p>
				</div>
				<div class="grooni-metabox-field">' . $this->renderField( $post_id ) . '</div>
			</div>';
	}

	public function renderField( $post_id ) {
		return '<input type="hidden" name="' . $this->getName() . '" value="' . $this->getValue( $post_id ) . '">';
	}

	public function getValueNew() {
		if ( isset( $_POST[ $this->getName() ] ) ) {
			return esc_attr( wp_unslash( $_POST[ $this->getName() ] ) );
		}

		return null;
	}

	public function getValue( $post_id = null ) {
		if ( $this->isAddPage() ) {
			return $this->getDefault();
		}
		if ( is_null( $post_id ) && $this->isEditPage() ) {
			$post_id = get_the_ID();
		}

		if ( ! isset( self::$values[ $post_id ] ) ) {
			$grooni_meta = get_post_meta( $post_id, 'grooni_meta', true );
			if ( ! empty( $grooni_meta ) && is_string( $grooni_meta ) ) {
				self::$values[ $post_id ] = json_decode( $grooni_meta, true );
			}
		}
		if ( isset( self::$values[ $post_id ][ $this->getName() ] ) ) {
			return self::$values[ $post_id ][ $this->getName() ];
		}

		return $this->getDefault();
	}

	protected function isEditPage() {
		global $pagenow;
		if ( ! is_admin() ) {
			return false;
		}

		return in_array( $pagenow, array( 'post.php', ) );
	}

	protected function isAddPage() {
		global $pagenow;
		if ( ! is_admin() ) {
			return false;
		}

		return in_array( $pagenow, array( 'post-new.php', ) );
	}

}
