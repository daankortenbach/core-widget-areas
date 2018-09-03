<?php
/**
 * Register, unregister and display widget areas through configuration.
 *
 * @package   D2\Core
 * @author    Craig Simpson <craig@craigsimpson.scot>
 * @copyright 2018, Craig Simpson
 * @license   MIT
 */

namespace D2\Core;

/**
 * Register, unregister or display widget areas.
 *
 * Example config (usually located at config/defaults.php):
 *
 * ```
 * use D2\Core\WidgetArea;
 *
 * $d2_widget_areas = [
 *     WidgetArea::REGISTER   => [
 *         [
 *             WidgetArea::ID          => 'utility-bar',
 *             WidgetArea::NAME        => __( 'Utility Bar', 'example-textdomain' ),
 *             WidgetArea::DESCRIPTION => __( 'Utility bar appearing above the site header.', 'example-textdomain' ),
 *             WidgetArea::LOCATION    => 'genesis_before_header',
 *             WidgetArea::BEFORE      => '<div class="utility-bar">',
 *             WidgetArea::AFTER       => '</div>',
 *             WidgetArea::PRIORITY    => 5,
 *         ],
 *     ],
 *     WidgetArea::UNREGISTER => [
 *         WidgetArea::HEADER_RIGHT,
 *         WidgetArea::SIDEBAR_ALT,
 *     ],
 * ];
 *
 * return [
 *     WidgetArea::class => $d2_widget_areas,
 * ];
 * ```
 *
 * @package D2\Core
 */
class WidgetArea extends Core {

	const REGISTER = 'register';
	const UNREGISTER = 'unregister';
	const ID = 'id';
	const NAME = 'name';
	const DESCRIPTION = 'description';
	const LOCATION = 'location';
	const BEFORE = 'before';
	const AFTER = 'after';
	const BEFORE_TITLE = 'before_title';
	const AFTER_TITLE = 'after_title';
	const PRIORITY = 'priority';
	const HEADER_RIGHT = 'header-right';
	const SIDEBAR = 'sidebar';
	const SIDEBAR_ALT = 'sidebar-alt';

	/**
	 * Register, unregister or display widget areas through configuration.
	 *
	 * @since 0.2.0
	 *
	 * @return void
	 */
	public function init() {
		if ( array_key_exists( self::REGISTER, $this->config ) ) {
			$this->register( $this->config[ self::REGISTER ] );
			$this->display( $this->config[ self::REGISTER ] );
		}

		if ( array_key_exists( self::UNREGISTER, $this->config ) ) {
			add_action( 'widgets_init', [ $this, 'unregister' ], 11 );
		}
	}

	/**
	 * Register widget areas.
	 *
	 * @since 0.2.0
	 *
	 * @link  https://codex.wordpress.org/Function_Reference/register_sidebar
	 * @link  genesis/lib/functions/widgetize.php.
	 *
	 * @param array $config Register config.
	 *
	 * @return array
	 */
	protected function register( $config ) {
		$register_function = $this->is_genesis() ? 'genesis_register_widget_area' : 'register_sidebar';

		return array_map( $register_function, $config );
	}

	/**
	 * Unregister widget areas.
	 *
	 * @since 0.2.0
	 *
	 * @return array
	 */
	public function unregister() {
		return array_map( 'unregister_sidebar', $this->config[ self::UNREGISTER ] );
	}

	/**
	 * Displays widget areas.
	 *
	 * @since 0.2.0
	 *
	 * @param array $config Register config.
	 *
	 * @return void
	 */
	protected function display( $config ) {
		foreach ( $config as $widget_area => $args ) {
			if ( ! array_key_exists( self::LOCATION, $args ) ) {
				return;
			}

			add_action( $args[ self::LOCATION ], function () use ( $args ) {
				$display_function = $this->is_genesis() ? 'genesis_widget_area' : 'dynamic_sidebar';
				$before           = $args[ self::BEFORE ] ? $args[ self::BEFORE ] : '<div class="' . $args[ self::ID ] . ' widget-area"><div class="wrap">';
				$after            = $args[ self::AFTER ] ? $args[ self::AFTER ] : '</div></div>';

				$display_function( $args[ self::ID ], [
					self::BEFORE => is_callable( $before ) ? $before() : $before,
					self::AFTER  => is_callable( $after ) ? $after() : $after,
				] );
			}, $args[ self::PRIORITY ] ? $args[ self::PRIORITY ] : 10 );
		}
	}

	/**
	 * Check for Genesis child theme.
	 *
	 * @since 0.2.0
	 *
	 * @return bool
	 */
	protected function is_genesis() {
		$theme = wp_get_theme();

		return 'genesis' === $theme->get( 'Template' ) ? true : false;
	}
}
