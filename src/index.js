/**
 * WordPress dependencies
 */
import { registerBlockType } from "@wordpress/blocks";
import { addFilter } from "@wordpress/hooks";
import { InspectorControls } from "@wordpress/block-editor";
import { PanelBody, ToggleControl } from "@wordpress/components";
import { createHigherOrderComponent } from "@wordpress/compose";
import { __ } from "@wordpress/i18n";

/**
 * Internal dependencies
 */
import "./style.scss";
import "./view.scss";
import Edit from "./edit";
import metadata from "./block.json";

const megaMenuIcon = (
	<svg
		width="24px"
		height="24px"
		viewBox="0 0 24 24"
		version="1.1"
		xmlns="http://www.w3.org/2000/svg"
	>
		<path d="M20,12 L4,12 L4,13.5 L20,13.5 L20,12 Z M10,6.5 L4,6.5 L4,8 L10,8 L10,6.5 Z M20,17.5 L4,17.5 L4,19 L20,19 L20,17.5 Z M20,5.62462724 L16.000015,9 L12,5.62462724 L12.9791165,4.5 L16.000015,7.04920972 L19.0208935,4.5 L20,5.62462724 Z"></path>
	</svg>
);

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
registerBlockType(metadata.name, {
	icon: megaMenuIcon,
	edit: Edit,
});

/**
 * Make the Mega Menu Block available to Navigation blocks.
 *
 * @since 0.1.0
 *
 * @param {Object} blockSettings The original settings of the block.
 * @param {string} blockName     The name of the block being modified.
 * @return {Object} The modified settings for the Navigation block or the original settings for other blocks.
 */
const addToNavigation = (blockSettings, blockName) => {
	if (blockName === "core/navigation") {
		return {
			...blockSettings,
			allowedBlocks: [
				...(blockSettings.allowedBlocks ?? []),
				"outermost/mega-menu",
			],
		};
	}
	return blockSettings;
};
addFilter(
	"blocks.registerBlockType",
	"outermost-mega-menu-add-to-navigation",
	addToNavigation,
);

/**
 * Add hover toggle control to Navigation block settings.
 *
 * @since 0.1.0
 */
const withMegaMenuHoverControl = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		if (props.name !== "core/navigation") {
			return <BlockEdit {...props} />;
		}

		const { attributes, setAttributes } = props;
		const { megaMenuHoverEnabled = false } = attributes;

		return (
			<>
				<BlockEdit {...props} />
				<InspectorControls>
					<PanelBody title={__("Mega Menu Settings", "mega-menu-block")}>
						<ToggleControl
							label={__("Enable hover for mega menus", "mega-menu-block")}
							checked={megaMenuHoverEnabled}
							onChange={(value) =>
								setAttributes({ megaMenuHoverEnabled: value })
							}
							help={__(
								"Open mega menus on hover instead of click only.",
								"mega-menu-block",
							)}
							__nextHasNoMarginBottom
						/>
					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}, "withMegaMenuHoverControl");

addFilter(
	"editor.BlockEdit",
	"outermost/mega-menu-hover-control",
	withMegaMenuHoverControl,
);
