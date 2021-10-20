<?php
/**
 * IPA Shortcode Class
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class IPA_Auction_Shortcode {

    public function ipa_auction_display_shortcode( $atts, $content = null ) {

        extract( shortcode_atts(
            array(
                'link'   => '#',
                'target' => '',
            ), $atts
        ) );

        return '<a class="real-btn btn-mini" href="' . $link . '" target="' . $target . '">' . do_shortcode( $content ) . '</a>';
    }

}