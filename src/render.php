<?php
/**
 * Server-side rendering for the Mega Menu block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 */

$disable_when_collapsed = ! empty( $attributes['disableWhenCollapsed'] );
$label_raw              = (string) ( $attributes['label'] ?? '' );
$label                  = wp_kses_post( $label_raw );
$label_plain            = trim( wp_strip_all_tags( $label_raw ) );
$description            = sanitize_text_field( (string) ( $attributes['description'] ?? '' ) );
$title                  = sanitize_text_field( (string) ( $attributes['title'] ?? '' ) );
$menu_slug              = sanitize_title( (string) ( $attributes['menuSlug'] ?? '' ) );
$collapsed_url          = esc_url( (string) ( $attributes['collapsedUrl'] ?? '' ) );
$justify_menu_raw       = sanitize_key( (string) ( $attributes['justifyMenu'] ?? '' ) );
$menu_width_raw         = sanitize_key( (string) ( $attributes['width'] ?? 'content' ) );
$justify_menu           = in_array( $justify_menu_raw, array( 'left', 'center', 'right' ), true ) ? $justify_menu_raw : '';
$menu_width             = in_array( $menu_width_raw, array( 'content', 'wide', 'full' ), true ) ? $menu_width_raw : 'content';

// Do not render if the required link label or menu template slug are missing.
if ( '' === $label_plain || '' === $menu_slug ) {
	return null;
}

$classes = array();
if ( $disable_when_collapsed ) {
	$classes[] = 'disable-menu-when-collapsed';
}
if ( $collapsed_url ) {
	$classes[] = 'has-collapsed-link';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $classes ),
	)
);

$menu_classes = array(
	'wp-block-outermost-mega-menu__menu-container',
	'menu-width-' . $menu_width,
);
if ( $justify_menu ) {
	$menu_classes[] = 'menu-justified-' . $justify_menu;
}

$menu_panel_id = wp_unique_id( 'outermost-mega-menu-panel-' );

$close_icon  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"></path></svg>';
$toggle_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 12" width="12" height="12" aria-hidden="true" focusable="false" fill="none"><path d="M1.50002 4L6.00002 8L10.5 4" stroke-width="1.5"></path></svg>';

$menu_opened_context = array(
	'menuOpenedBy' => (object) array(),
);

$menu_context = array(
	'menuWidth' => false,
	'menuLeft'  => false,
);

$menu_content = '';
if ( function_exists( 'block_template_part' ) ) {
	ob_start();
	$template_part_result = block_template_part( $menu_slug );
	$buffered_menu_output = ob_get_clean();
	$menu_content         = is_string( $template_part_result ) && '' !== $template_part_result ? $template_part_result : $buffered_menu_output;
}
?>

<li
	<?php echo $wrapper_attributes; ?>
	data-wp-interactive="outermost/mega-menu"
	<?php echo wp_interactivity_data_wp_context( $menu_opened_context ); ?>
	data-wp-on--focusout="actions.handleMenuFocusout"
	data-wp-on--keydown="actions.handleMenuKeydown"
	data-wp-on--mouseover="actions.handleMenuMouseover"
	data-wp-on--mouseleave="actions.handleMenuMouseleave"
	data-wp-on-async-window--resize="callbacks.updateWindowDimensions"
	data-wp-watch="callbacks.initMenu"
>
	<button
		type="button"
		class="wp-block-outermost-mega-menu__toggle"
		data-wp-on--click="actions.toggleMenuOnClick"
		data-wp-bind--aria-expanded="state.isMenuOpen"
		aria-controls="<?php echo esc_attr( $menu_panel_id ); ?>"
		aria-haspopup="menu"
		<?php if ( '' !== $title ) : ?>
			title="<?php echo esc_attr( $title ); ?>"
		<?php endif; ?>
	>
		<span class="wp-block-navigation-item__label"><?php echo $label; ?></span>
		<span class="wp-block-outermost-mega-menu__toggle-icon"><?php echo $toggle_icon; ?></span>
		<?php if ( '' !== $description ) : ?>
			<span class="wp-block-navigation-item__description"><?php echo esc_html( $description ); ?></span>
		<?php endif; ?>
	</button>

	<div
		id="<?php echo esc_attr( $menu_panel_id ); ?>"
		class="<?php echo esc_attr( implode( ' ', $menu_classes ) ); ?>"
		<?php echo wp_interactivity_data_wp_context( $menu_context ); ?>
		data-wp-init="callbacks.initMenuPosition"
		data-wp-watch="callbacks.watchWindowChanges"
		data-wp-style--width="context.menuWidth"
		data-wp-style--left="context.menuLeft"
		data-wp-bind--aria-hidden="state.isMenuOpen ? 'false' : 'true'"
		tabindex="-1"
	>
		<?php echo $menu_content; ?>
		<button
			aria-label="<?php echo esc_attr__( 'Close menu', 'mega-menu-block' ); ?>"
			class="menu-container__close-button"
			data-wp-on--click="actions.closeMenuOnClick"
			type="button"
		>
			<?php echo $close_icon; ?>
		</button>
	</div>

	<?php if ( $disable_when_collapsed && $collapsed_url ) : ?>
		<a class="wp-block-outermost-mega-menu__collapsed-link" href="<?php echo esc_url( $collapsed_url ); ?>">
			<span class="wp-block-navigation-item__label"><?php echo esc_html( $label_plain ); ?></span>
		</a>
	<?php endif; ?>
</li>
