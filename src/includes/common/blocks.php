<?php

/**
 * bbPress Blocks
 *
 * @package bbPress
 * @subpackage Blocks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BBP_Blocks' ) ) :
/**
 * bbPress Shortcode Class
 *
 * @since 2.0.0 bbPress (r3031)
 */
class BBP_Blocks {

	// Vars

	/**
	 * @var BBP_Shortcodes Most of our blocks are just visual representations of existing shortcodes.
	 */
	public $shortcodes;

	// Methods

	/**
	 * Set up the blocks for the Block Editor.
	 * 
	 * @since 2.7.0 Added the `bbp-blocks` script.
	 * 
	 * @param BBP_Shortcodes $shortcodes The shortcodes class.
	 * @return void
	 * 
	 */
	public function __construct( BBP_Shortcodes $shortcodes = null ) {
	
		$this->shortcodes = $shortcodes;

		if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
			add_filter( 'block_categories_all', array( $this, 'register_bbp_category' ) );
		} else {
			add_filter( 'block_categories', array( $this, 'register_bbp_category' ) );
		}

		$this->register_bbp_blocks();
	}

	public function register_bbp_category( $categories ) {

		$categories[] = array(
		'slug'  => 'bbpress',
		'title' => 'bbPress'
		);

		return $categories;
	}

	/**
	 * Register the bbPress blocks.
	 * 
	 * @since 2.7.0 Added the `bbp-blocks` script.
	 * 
	 * @return void
	 */
	public function register_bbp_blocks() {

		if ( ! function_exists('register_block_type') ) {
			return;
		}

		wp_register_style(
		'bbp-blocks',
		plugins_url('admin/assets/css/blocks.css', __DIR__),
		array(),
		filemtime(dirname(__DIR__) . '/admin/assets/css/blocks.css')
		);

		wp_register_script(
		'bbp-blocks',
		plugins_url('admin/assets/js/blocks.js', __DIR__),
		array(
			'wp-blocks',
			'wp-components',
			'wp-i18n',
			'wp-element',
			'wp-block-editor',
			'lodash'
		),
		filemtime(dirname(__DIR__) . '/admin/assets/js/blocks.js')
		);

		wp_localize_script(
		'bbp-blocks', 
		'bbpBlocks', 
		array(
			'data' => array(
				'forum_post_type' => bbp_get_forum_post_type(),
				'forum_count' => wp_count_posts( bbp_get_forum_post_type() )->publish,
			)
		)
		);

		// Register blocks
		$blocks = array(
		'forum-index', 
		'forum-form', 
		'single-forum', 
		'topic-index', 
		'topic-form',
		'single-topic', 
		'topic-tags',  
		'single-tag',
		'reply-form', 
		'single-reply',
		'single-view', 
		'search-form', 
		'search', 
		'login', 
		'register', 
		'lost-pass', 
		'stats'
		);

		foreach ( $blocks as $block ) {
			$formatted_block = str_replace( '-', '_', $block );
			register_block_type(
			"bbpress/$block", 
			array(
				'render_callback' => array($this, "display_$formatted_block"),
				'editor_script' => 'bbp-blocks',
				'editor_style' => 'bbp-blocks',
			)
			);
		}

	}

	/**
	 * Display function for `display_forum_index`
	 * The forum index view.
	 * 
	 * @return string The markup for the forum list view.
	 */
	public function display_forum_index() {
		return $this->shortcodes->display_forum_index();
	}

	/**
	 * Display function for `display_forum_form` -- the new forum form.
	 *
	 * @return string The markup for the new forum form.
	 */
	public function display_forum_form() {
		return $this->shortcodes->display_forum_form();
	}

	/**
	 * Display function for single forum view.
	 *
	 * @param array $attributes Block attributes
	 * @return string The markup for the single forum
	 */
	public function display_single_forum( $attributes ) {
		// Ensure ID is properly sanitized and exists
		$forum_id = ! empty( $attributes['id'] ) ? absint( $attributes['id'] ) : 0;
	
		if ( ! $forum_id || ! bbp_is_forum( $forum_id ) ) {
			return $this->display_forum_index();
		}

		if ( ! empty ( $attributes['topics_per_page'] ) ) {
			add_filter( 
				'bbp_get_topics_per_page', 
				function () use ( $attributes ) {
					return absint( $attributes['topics_per_page'] );
				}
			);
		}

		return $this->shortcodes->display_forum( 
			array(
				'id' => $forum_id,
			) 
		);
	}

	/**
	 * Display function for `display_topic_index`
	 * The topic list view.
	 *
	 * @return string The markup for the topic list view.
	 */
	public function display_topic_index() {
		return $this->shortcodes->display_topic_index();
	}

	/**
	 * Display function for `display_topic_form` 
	 * The new topic form.
	 *
	 * @return string The markup for the new topic form.
	 */
	public function display_topic_form( $attributes ) {
		// Ensure ID is properly sanitized and exists
		$forum_id = ! empty( $attributes['id'] ) ? absint( $attributes['id'] ) : 0;

		return $this->shortcodes->display_topic_form( array( 'forum_id' => $forum_id ) );
	}

	/**
	 * Display function for `display_topic` 
	 * The single topic view.
	 *
	 * @param $attributes (array) An array with at minimum an `id` 
	 * key set to the topic id.
	 * @return string The markup for the single topic.
	 */
	public function display_single_topic( $attributes ) {
		return $this->shortcodes->display_topic( $attributes );
	}

	/**
	 * Display function for `display_topic_tags` 
	 * The topic tag list view.
	 *
	 * @return string The markup for the topic tag list view.
	 */
	public function display_topic_tags() {
		return $this->shortcodes->display_topic_tags();
	}

	/**
	 * Display function for `display_topics_of_tag` 
	 * The topics of a single tag view.
	 * 
	 * @param $attributes (array) An array with at minimum a slug 
	 * key set to the tag slug.	
	 * @return string The markup for the topics of a single tag.
	 */
	public function display_single_tag( $attributes ) {
		return $this->shortcodes->display_topics_of_tag( $attributes );
	}

	/**
	 * Display function for `display_reply_form` 
	 * The new reply form.
	 *
	 * @return string The markup for the new reply form.
	 */
	public function display_reply_form() {
		return $this->shortcodes->display_reply_form();
	}

	/**
	 * Display function for `display_reply` 
	 * The single reply view.
	 *
	 * @param $attributes (array) An array with at minimum an `id` key 
	 * set to the reply id.
	 * @return string The markup for the single reply.
	 */
	public function display_single_reply( $attributes ) {
		return $this->shortcodes->display_reply( $attributes );
	}

	/**
	 * Display function for `display_view` 
	 * The single view view.
	 *
	 * @param $attributes (array) An array with at minimum an `id` key 
	 * set to the view id.
	 * @return string The markup for the single view.
	 */
	public function display_single_view( $attributes ) {
		return $this->shortcodes->display_view( $attributes );
	}

	/**
	 * Display function for `display_search_form` 
	 * The search form.
	 *
	 * @return string The markup for the search form.
	 */
	public function display_search_form() {
		return $this->shortcodes->display_search_form();
	}

	/**
	 * Display function for `display_search` 
	 * The search results view.
	 *
	 * @param $attributes (array) An array with at minimum a `query` key 
	 * set to the search query.
	 * @return string The markup for the search results.
	 */
	public function display_search( $attributes ) {
		return $this->shortcodes->display_search( $attributes );
	}

	/**
	 * Display function for `display_login` 
	 * The login form.
	 *
	 * @return string The markup for the login form.
	 */
	public function display_login() {
		return $this->shortcodes->display_login();
	}

	/**
	 * Display function for `display_register` 
	 * The registration form.
	 *
	 * @return string The markup for the registration form.
	 */
	public function display_register() {
		return $this->shortcodes->display_register();
	}

	/**
	 * Display function for `display_lost_pass` 
	 * The lost password form.
	 *
	 * @return string The markup for the lost password form.
	 */
	public function display_lost_pass() {
		return $this->shortcodes->display_lost_pass();
	}

	/**
	 * Display function for `display_stats` 
	 * The forum statistics view.
	 *
	 * @return string The markup for the forum statistics.
	 */
	public function display_stats() {
		return $this->shortcodes->display_stats();
	}

}
endif;
