<?php

/**
 * Plugin Name:       Mega Menu Block
 * Description:       An exploratory mega menu block
 * Requires at least: 6.5
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Nick Diego
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mega-menu-block
 *
 * @package           mega-menu-block
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function outermost_mega_menu_block_init()
{
	register_block_type(__DIR__ . '/build');
}
add_action('init', 'outermost_mega_menu_block_init');

/**
 * Adds a custom template part area for mega menus to the list of template part areas.
 *
 * This function introduces a new area specifically for menu templates, allowing
 * the creation of sections within a mega menu. The new area is appended to the
 * existing list of template part areas.
 *
 * @see https://developer.wordpress.org/reference/hooks/default_wp_template_part_areas/
 *
 * @param array $areas Existing array of template part areas.
 * @return array Modified array of template part areas including the new mega menu area.
 */
function outermost_mega_menu_template_part_areas(array $areas)
{
	$areas[] = array(
		'area'        => 'menu',
		'area_tag'    => 'div',
		'description' => __('Menu templates are used to create sections of a mega menu.', 'mega-menu-block'),
		'icon' 		  => 'layout',
		'label'       => __('Menu', 'mega-menu-block'),
	);

	return $areas;
}
add_filter('default_wp_template_part_areas', 'outermost_mega_menu_template_part_areas');

/**
 * Add hover control attribute to Navigation block.
 *
 * @param array  $args       Block type args for registration.
 * @param string $block_type Block type name.
 * @return array Filtered block type args.
 */
function outermost_mega_menu_add_navigation_hover_attribute($args, $block_type)
{
	if ('core/navigation' !== $block_type) {
		return $args;
	}

	if (! isset($args['attributes'])) {
		$args['attributes'] = array();
	}

	$args['attributes']['megaMenuHoverEnabled'] = array(
		'type' => 'boolean',
		'default' => false,
	);

	$args['attributes']['megaMenuDebugMode'] = array(
		'type' => 'boolean',
		'default' => false,
	);

	return $args;
}
add_filter('register_block_type_args', 'outermost_mega_menu_add_navigation_hover_attribute', 10, 2);

/**
 * Add hover context to Navigation block wrapper.
 *
 * @param string $block_content Block HTML content.
 * @param array  $block         Block data.
 * @return string Modified block content.
 */
/**
 * Add hover context to Navigation block wrapper.
 *
 * @param string $block_content Block HTML content.
 * @param array  $block         Block data.
 * @return string Modified block content.
 */
function outermost_mega_menu_navigation_render_block($block_content, $block)
{
	if ('core/navigation' !== $block['blockName']) {
		return $block_content;
	}

	$hover_enabled = isset($block['attrs']['megaMenuHoverEnabled']) && $block['attrs']['megaMenuHoverEnabled'] ? 'true' : 'false';
	$debug_mode = isset($block['attrs']['megaMenuDebugMode']) && $block['attrs']['megaMenuDebugMode'] ? 'true' : 'false';

	// Use WordPress HTML API to safely add attributes
	$processor = new WP_HTML_Tag_Processor($block_content);

	if ($processor->next_tag('nav')) {
		$processor->set_attribute('data-mega-menu-hover-enabled', $hover_enabled);
		$processor->set_attribute('data-mega-menu-debug-mode', $debug_mode);
		return $processor->get_updated_html();
	}

	return $block_content;
}
add_filter('render_block', 'outermost_mega_menu_navigation_render_block', 10, 2);
add_filter('render_block', 'outermost_mega_menu_navigation_render_block', 10, 2);
