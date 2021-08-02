<?php
/**
 * Plugin Name: Disable Product review comment
 * Description: WooCommerce extension that lets you disable product review comments and allows you cange the rating button text.
 * Version: 1.0.0
 * Author: AashikP
 * Author URI: https://aashikp.com
 * Text Domain: disable-product-review-comment
 * Requires at least: 5.0.0
 * Requires PHP: 7.3
 * WC requires at least: 4.0.0
 * WC tested up to: 5.4.1
 *
 * @package Disable Product Review Comment
 */

defined( 'ABSPATH' ) || exit;
/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	/**
	 * Disable Product Review Comment Settings (WooCommerce > Settings > Products)
	 *
	 * @param array $settings -> Add to WooCommerce Settings.
	 */
	function disable_product_review_comment_settings( $settings ) {

		$settings[] = array(
			'title' => __( 'Review Rating Control', 'disable-product-review-comment' ),
			'type'  => 'title',
			'id'    => 'ap_disable_comment',
		);

		$settings[] = array(
			'title'   => __( 'Disable Comments?', 'disable-product-review-comment' ),
			'desc'    => __( 'Disable comments and only enable rating', 'disable-product-review-comment' ),
			'id'      => 'disable_product_review_comment_wc',
			'default' => 'no',
			'type'    => 'checkbox',
		);

		// Coupon to be applied.
		$settings[] = array(
			'title'    => __( 'Rename rating submit button', 'disable-product-review-comment' ),
			'desc'     => __( 'Enter the button label for rating submission. Default is `Submit Rating`', 'disable-product-review-comment' ),
			'id'       => 'product_rating_submit_button',
			'default'  => null,
			'type'     => 'text',
			'desc_tip' => true,
			'css'      => 'width:200px;',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'ap_disable_comment',
		);

		return $settings;
	}
	add_filter( 'woocommerce_products_general_settings', 'disable_product_review_comment_settings' );

	/**
	 * Check that only star option is displayed
	 */
	function is_star_only() {
		$review_control = get_option( 'disable_product_review_comment_wc', 'Submit Rating' );
		if ( 'yes' === $review_control ) {
			return true;
		}
		return false;
	}

	if ( is_star_only() ) {
		add_filter( 'woocommerce_product_review_comment_form_args', 'ap_update_comment_form' );
		add_filter( 'allow_empty_comment', '__return_true' );
		add_filter( 'comment_duplicate_message', 'ap_duplicate_rating_error' );
	}

	/**
	 * Duplicate rating message.
	 */
	function ap_duplicate_rating_error() {
		return __( 'Duplicate rating detected; It looks like you\'ve already rated.', 'disable-product-review-comment' );
	}

	/**
	 * Reset the review form to only include the rating part.
	 */
	function ap_update_comment_form() {
		$comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating">' . esc_html__( 'Your rating', 'woocommerce' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating" required>
		<option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce' ) . '</option>
		<option value="5">' . esc_html__( 'Perfect', 'woocommerce' ) . '</option>
		<option value="4">' . esc_html__( 'Good', 'woocommerce' ) . '</option>
		<option value="3">' . esc_html__( 'Average', 'woocommerce' ) . '</option>
		<option value="2">' . esc_html__( 'Not that bad', 'woocommerce' ) . '</option>
		<option value="1">' . esc_html__( 'Very poor', 'woocommerce' ) . '</option>
		</select></div>';
		return $comment_form;
	}

	if ( get_option( 'product_rating_submit_button', 'Submit Rating' ) ) {
		add_filter( 'comment_form_defaults', 'ap_update_rating_button_label' );
	}


	/**
	 * Change the button label for rating from `Post Comment` to `Submit Rating`
	 *
	 * @param array $defaults - default values.
	 */
	function ap_update_rating_button_label( $defaults ) {
		if ( is_product() ) {
			$defaults['label_submit']  = strval( get_option( 'product_rating_submit_button', 'Submit Rating' ) );
			$defaults['submit_button'] = '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="' . $defaults['label_submit'] . '" />';
			return $defaults;
		}
	}
} else {
	/**
	 * WooCommerce ShipStation fallback notice.
	 */
	function ap_admin_notice() {
		?>
		<div class="error">
		<p>
			<?php
			esc_html_e( 'Disable Product Review Comment for WooCommerce requires WooCommerce to be installed and active.', 'disable-product-review-comment' );
			?>
		</p>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'ap_admin_notice' );
}
