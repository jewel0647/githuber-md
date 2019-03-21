<?php
/**
 * Class Register
 *
 * @author Terry Lin
 * @link https://terryl.in/
 *
 * @package Githuber
 * @since 1.0.0
 * @version 1.7.0
 */

namespace Githuber\Controller;

class Register extends ControllerAbstract {

	/**
	 * Constructer.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Initialize.
	 */
	public function init() {

		add_action( 'admin_init', array( $this, 'admin_init' ) );

		$this->register_hooks();

		if ( 'yes' === githuber_get_option( 'disable_revision', 'githuber_markdown' ) ) {
			add_action( 'init', array( $this , 'remove_revisions' ), 10 );
		}

		if ( 'yes' === githuber_get_option( 'disable_autosave', 'githuber_markdown' ) ) {
			add_action( 'wp_print_scripts', array( $this , 'remove_autosave' ), 10 );
		}

		$this->version_migration();
	}

	/**
	 * Initalize to WP `admin_init` hook.
	 */
	function admin_init() {
		global $current_user;

		if ( user_can_richedit() ) {
			update_user_option( $current_user->ID, 'rich_editing', 'false', true );
		}
		add_filter( 'user_can_richedit' , '__return_false', 50 );
	}

	/**
	 * Migration.
	 */
	public function version_migration() {

		$migration_v162 = get_option( 'githuber_migration_v162');

		if ( empty( $migration_v162 ) ) {

			$githuber_modules = array(
				'support_prism'            => githuber_get_option( 'support_prism', 'githuber_markdown' ),
				'support_katex'            => githuber_get_option( 'support_katex', 'githuber_markdown' ),
				'support_flowchart'        => githuber_get_option( 'support_flowchart', 'githuber_markdown' ),
				'support_sequence_diagram' => githuber_get_option( 'support_sequence_diagram', 'githuber_markdown' ),
				'support_mermaid'          => githuber_get_option( 'support_mermaid', 'githuber_markdown' ),
				'support_image_paste'      => githuber_get_option( 'support_image_paste', 'githuber_markdown' ),
			);

			$githuber_extensions = array(
				'support_task_list' => githuber_get_option( 'support_task_list', 'githuber_markdown' ),
			);

			update_option( 'githuber_modules', $githuber_modules, '', 'yes' );
			update_option( 'githuber_extensions', $githuber_extensions, '', 'yes' );
			update_option( 'githuber_migration_v162', 'yes', '', 'yes' );
		}
	}

	/**
	 * Remove revisions.
	 */
	public function remove_revisions() {
		remove_post_type_support( 'post', 'revisions' );
		remove_post_type_support( 'page', 'revisions' );
		remove_post_type_support( 'repository', 'revisions' );
	}

	/**
	 * Remove auto-save function.
	 */
	function remove_autosave() {
		wp_deregister_script('autosave');
	}

	/**
	 * Register CSS style files.
	 */
	public function admin_enqueue_styles( $hook_suffix ) {

	}

	/**
	 * Register JS files.
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

	}
	
	/**
	 * Activate Githuber plugin.
	 */
	public function activate_plugin() {
		global $current_user;

		$githuber_markdown = array(
			'enable_markdown_for_post_types' => array( 'post' => 'page' ),
			'disable_revision'               => 'no',
			'disable_autosave'               => 'yes',
			'html_to_markdown'               => 'yes',
			'editor_live_preview'            => 'yes',
			'editor_sync_scrolling'          => 'yes',
			'editor_html_decode'             => 'yes',
			'editor_toolbar_theme'           => 'default',
			'editor_editor_theme'            => 'default',
		);

		// Add default setting. Only execute this action at the first time activation.
		if ( false === get_option( 'githuber_markdown' ) ) {
			update_option( 'githuber_markdown', $githuber_markdown, '', 'yes' );
			update_option( 'githuber_migration_v162', 'yes', '', 'yes' );
		}
	}

	/**
	 * Deactivate Githuber plugin.
	 */
	public function deactivate_plugin() {
		global $current_user;
		// Turn on Rich-text editor.
		update_user_option( $current_user->ID, 'rich_editing', 'true', true );
		delete_user_option( $current_user->ID, 'dismissed_wp_pointers', true );
	}

	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		register_activation_hook( $this->githuber_plugin_path, array( $this , 'activate_plugin' ) );
		register_deactivation_hook( $this->githuber_plugin_path, array( $this , 'deactivate_plugin' ) );
	}
}
