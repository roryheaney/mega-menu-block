<?php
/**
 * Plugin Name:       Mega Menu Block
 * Description:       An exploratory mega menu block
 * Requires at least: 6.5
 * Requires PHP:      7.0
 * Version:           0.2.0
 * Author:            Started by Nick Diego, continued / updated / maintained by Rory Heaney
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mega-menu-block
 *
 * @package           mega-menu-block
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the block using metadata loaded from block.json.
 */
function outermost_mega_menu_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'outermost_mega_menu_block_init' );

/**
 * Loads plugin translations.
 */
function outermost_mega_menu_load_textdomain() {
	load_plugin_textdomain(
		'mega-menu-block',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'outermost_mega_menu_load_textdomain' );

/**
 * Adds a custom template part area for mega menus.
 *
 * @param array $areas Existing array of template part areas.
 * @return array
 */
function outermost_mega_menu_template_part_areas( array $areas ) {
	$areas[] = array(
		'area'        => 'menu',
		'area_tag'    => 'div',
		'description' => __(
			'Menu templates are used to create sections of a mega menu.',
			'mega-menu-block'
		),
		'icon'        => 'layout',
		'label'       => __( 'Menu', 'mega-menu-block' ),
	);

	return $areas;
}
add_filter( 'default_wp_template_part_areas', 'outermost_mega_menu_template_part_areas' );

/**
 * Adds mega menu settings attributes to the Navigation block.
 *
 * @param array  $args       Block type args for registration.
 * @param string $block_type Block type name.
 * @return array
 */
function outermost_mega_menu_add_navigation_attributes( array $args, string $block_type ) {
	if ( 'core/navigation' !== $block_type ) {
		return $args;
	}

	if ( ! isset( $args['attributes'] ) ) {
		$args['attributes'] = array();
	}

	$args['attributes']['megaMenuHoverEnabled'] = array(
		'type'    => 'boolean',
		'default' => false,
	);

	$args['attributes']['megaMenuDebugMode'] = array(
		'type'    => 'boolean',
		'default' => false,
	);

	return $args;
}
add_filter(
	'register_block_type_args',
	'outermost_mega_menu_add_navigation_attributes',
	10,
	2
);

/**
 * Adds hover/debug context attributes to the Navigation wrapper.
 *
 * @param string $block_content Block HTML content.
 * @param array  $block         Parsed block data.
 * @return string
 */
function outermost_mega_menu_navigation_render_block( string $block_content, array $block ) {
	if ( empty( $block_content ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	if ( isset( $block['blockName'] ) && 'core/navigation' !== $block['blockName'] ) {
		return $block_content;
	}

	$hover_enabled = ! empty( $block['attrs']['megaMenuHoverEnabled'] ) ? 'true' : 'false';
	$debug_mode    = ! empty( $block['attrs']['megaMenuDebugMode'] ) ? 'true' : 'false';

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( $processor->next_tag( 'nav' ) ) {
		$processor->set_attribute( 'data-mega-menu-hover-enabled', $hover_enabled );
		$processor->set_attribute( 'data-mega-menu-debug-mode', $debug_mode );

		return $processor->get_updated_html();
	}

	return $block_content;
}
add_filter(
	'render_block_core/navigation',
	'outermost_mega_menu_navigation_render_block',
	10,
	2
);