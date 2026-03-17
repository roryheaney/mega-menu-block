/**
 * WordPress dependencies
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

// Utility function to convert CSS values (rem, em, %, etc.) to pixels
function convertCssValueToPixels( cssValue ) {
	if ( ! cssValue ) {
		return 0;
	}

	// Create a temporary element
	const tempElement = document.createElement( 'div' );

	// Apply the CSS value to the temporary element
	// For example, setting its width to the complex CSS value
	tempElement.style.width = cssValue;

	// Append the temporary element to the body to make it part of the document
	document.body.appendChild( tempElement );

	// Use getComputedStyle to get the computed width in pixels
	const computedWidth = window.getComputedStyle( tempElement ).width;

	// Remove the temporary element from the document
	document.body.removeChild( tempElement );

	// Return the computed width as a number
	return parseFloat( computedWidth );
}

const { state, actions } = store( 'outermost/mega-menu', {
	state: {
		// Client-only values for positioning - initialized in initMenuPosition callback
		windowWidth: 0,
		rootPaddingLeft: 0,
		rootPaddingRight: 0,
		get windowSpace() {
			// Derived state - pure getter (no side effects)
			return (
				state.windowWidth -
				state.rootPaddingLeft -
				state.rootPaddingRight
			);
		},
		get isMenuOpen() {
			// The menu is opened if any method is true.
			return (
				Object.values( state.menuOpenedBy ).filter( Boolean ).length > 0
			);
		},
		get menuOpenedBy() {
			const context = getContext();
			return context.menuOpenedBy;
		},
	},
	actions: {
		toggleMenuOnClick() {
			const context = getContext();
			const { ref } = getElement();

			// Safari won't send focus to the clicked element, so we need to manually place it: https://bugs.webkit.org/show_bug.cgi?id=22261
			// if ( window.document.activeElement !== ref ) ref.focus();

			if ( state.isMenuOpen ) {
				actions.closeMenu( 'closeButton' );
			} else {
				context.previousFocus = ref;
				actions.openMenu( 'click' );
			}
		},
		closeMenuOnClick() {
			actions.closeMenu( 'closeButton' );
		},
		handleMenuKeydown( event ) {
			if ( state.isMenuOpen && event?.key === 'Escape' ) {
				event.preventDefault();
				actions.closeMenu( 'closeButton' );
			}
		},
		handleMenuFocusout( event ) {
			const context = getContext();
			const menuContainer = context.megaMenu?.querySelector(
				'.wp-block-outermost-mega-menu__menu-container'
			);
			// If focus is outside menu, and in the document, close menu
			// event.target === The element losing focus
			// event.relatedTarget === The element receiving focus (if any)
			// When focusout is outside the document,
			// `window.document.activeElement` doesn't change.

			// The event.relatedTarget is null when something outside the navigation menu is clicked. This is only necessary for Safari.
			// TODO: There is still an issue in Safari where clicking on the menu link closes the menu. We don't want this. The toggleMenuOnClick callback should handle this.
			if (
				event.relatedTarget === null ||
				( ! menuContainer?.contains( event.relatedTarget ) &&
					event.target !== window.document.activeElement )
			) {
				actions.closeMenu( 'click' );
				actions.closeMenu( 'focus' );
				actions.closeMenu( 'hover' );
			}
		},
		handleMenuMouseover() {
			// Check if hover is enabled on parent Navigation
			const { ref } = getElement();

			// Look for the parent nav element (not the ul which also has wp-block-navigation class)
			const navBlock = ref.closest( 'nav' );
			const hoverEnabled =
				navBlock?.getAttribute( 'data-mega-menu-hover-enabled' ) ===
				'true';

			if ( ! hoverEnabled ) {
				return; // Hover disabled, do nothing
			}

			if (
				! document.documentElement.classList.contains(
					'has-modal-open'
				)
			) {
				actions.openMenu( 'hover' );
			}
		},
		handleMenuMouseleave() {
			actions.closeMenu( 'hover' );
		},
		openMenu( menuOpenedOn = 'click' ) {
			state.menuOpenedBy[ menuOpenedOn ] = true;
		},
		closeMenu( menuClosedOn = 'click' ) {
			const { ref } = getElement();

			// Check if debug mode is enabled.
			const navBlock = ref.closest( 'nav' );
			const debugMode =
				navBlock?.getAttribute( 'data-mega-menu-debug-mode' ) ===
				'true';

			// If debug mode is on and this is not the close button, ignore.
			if ( debugMode && menuClosedOn !== 'closeButton' ) {
				return;
			}

			const context = getContext();

			// Clear all open states when close button is clicked.
			if ( menuClosedOn === 'closeButton' ) {
				state.menuOpenedBy.click = false;
				state.menuOpenedBy.focus = false;
				state.menuOpenedBy.hover = false;
			} else {
				state.menuOpenedBy[ menuClosedOn ] = false;
			}

			// Reset the menu reference and button focus when fully closed.
			if ( ! state.isMenuOpen ) {
				if (
					context.megaMenu?.contains( window.document.activeElement )
				) {
					context.previousFocus?.focus();
				}
				context.previousFocus = null;
				context.megaMenu = null;
			}
		},
		updateRootPadding() {
			// Read CSS variables and convert to pixels
			const bodyStyles = window.getComputedStyle( document.body );
			const leftValue = bodyStyles
				.getPropertyValue( '--wp--style--root--padding-left' )
				.trim();
			const rightValue = bodyStyles
				.getPropertyValue( '--wp--style--root--padding-right' )
				.trim();

			state.rootPaddingLeft = convertCssValueToPixels( leftValue );
			state.rootPaddingRight = convertCssValueToPixels( rightValue );
		},
		calculateMenuPosition( menuElement, context ) {
			// Find parent navigation block
			const navBlock = menuElement.closest( '.wp-block-navigation' );
			if ( ! navBlock ) return;

			// Determine justification from CSS classes
			let justification = 'left';
			if (
				navBlock.classList.contains( 'items-justified-center' ) ||
				navBlock.classList.contains( 'items-justified-space-between' )
			) {
				justification = 'center';
			} else if (
				navBlock.classList.contains( 'items-justified-right' )
			) {
				justification = 'right';
			}

			// Check menu's own justification classes (overrides navigation)
			if ( menuElement.classList.contains( 'menu-justified-center' ) ) {
				justification = 'center';
			} else if (
				menuElement.classList.contains( 'menu-justified-right' )
			) {
				justification = 'right';
			} else if (
				menuElement.classList.contains( 'menu-justified-left' )
			) {
				justification = 'left';
			}

			// Calculate positions
			const windowSpace = state.windowSpace; // Uses derived state
			const menuWidth = menuElement.offsetWidth;
			const menuRect = menuElement.getBoundingClientRect();
			const navBlockRect = navBlock.getBoundingClientRect();

			// Calculate left offset
			const leftOffset =
				navBlockRect.left <= state.rootPaddingLeft
					? state.rootPaddingLeft - navBlockRect.left
					: navBlockRect.left - state.rootPaddingLeft;

			// Apply positioning logic based on justification
			if ( justification === 'center' ) {
				const leftSpace = ( windowSpace - menuWidth ) / 2;

				if ( menuWidth > windowSpace ) {
					context.menuWidth = `${ windowSpace }px`;
					context.menuLeft = `-${ leftOffset }px`;
				} else if ( menuRect.left > 0 && leftSpace >= menuRect.left ) {
					context.menuWidth = false; // false removes inline style
					context.menuLeft = false;
				} else if ( leftOffset >= leftSpace ) {
					context.menuWidth = false;
					context.menuLeft = `-${ leftOffset - leftSpace }px`;
				} else {
					context.menuWidth = false;
					context.menuLeft = `${ leftSpace - leftOffset }px`;
				}
			} else if (
				justification === 'left' ||
				justification === 'right'
			) {
				if ( menuWidth > windowSpace ) {
					context.menuWidth = `${ windowSpace }px`;
					context.menuLeft = `${ leftOffset }px`;
				} else {
					context.menuWidth = false; // false removes inline style

					if ( menuRect.left <= 0 ) {
						context.menuLeft = `${ leftOffset }px`;
					} else {
						context.menuLeft = false; // false removes inline style
					}
				}
			}
		},
	},
	callbacks: {
		initMenu() {
			const { ref } = getElement();

			// Set the menu reference when initialized.
			if ( state.isMenuOpen ) {
				const context = getContext();
				context.megaMenu = ref;
			}
		},
		initMenuPosition() {
			// Initialize window dimensions if not already set (first menu only)
			if ( state.windowWidth === 0 ) {
				state.windowWidth = window.innerWidth;
				actions.updateRootPadding(); // CRITICAL: Always initialize padding
			}
			// Do NOT calculate position here - let watchWindowChanges handle it
		},
		updateWindowDimensions() {
			// Single handler for window resize - updates global state only
			state.windowWidth = window.innerWidth;
			actions.updateRootPadding();
		},
		watchWindowChanges() {
			// Per-menu watcher - handles ALL position calculations (initial + updates)
			const { ref } = getElement();

			// Guard against null ref during hydration
			if ( ! ref ) return;

			const context = getContext();
			// CRITICAL: Read all state dependencies for proper tracking
			// wp-watch only re-runs when state/context accessed inside this callback changes
			const width = state.windowWidth;
			void state.rootPaddingLeft;
			void state.rootPaddingRight;

			// Calculate position whenever state changes (including first run)
			if ( width > 0 ) {
				actions.calculateMenuPosition( ref, context );
			}
		},
	},
} );
