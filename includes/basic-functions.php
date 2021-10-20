<?php
/**
 * IPA Shortcode Class
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ipa_auction_display( $content ) {

    global $post;
    $post_id = $post->ID;
    $post_author_id = $post->post_author;
    $current_user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $post_author_email = get_the_author_meta( 'email', $post_author_id );
    $auction_status = get_post_meta( $post_id, 'ipa_auction_status', true );
    $auction_title = get_post_meta( $post_id, 'ipa_auction_title', true );
    $auction_description = get_post_meta( $post_id, 'ipa_auction_description', true );
    $auction_starting_price = get_post_meta( $post_id, 'ipa_auction_start_price', true );
    $auction_end_date = get_post_meta( $post_id, 'ipa_auction_end_date', true );
    $auction_bids = get_post_meta( $post_id, 'ipa_auction_bids', true );
    $auction_bids_email_list = get_post_meta( $post_id, 'ipa_bid_emailing_list', true );
    $current_time = date('Y-m-d H:i:s');

    $auction_current_bid = get_post_meta( $post_id, 'ipa_auction_current', true );
    if( ! is_array( $auction_current_bid ) || count( $auction_current_bid ) < 1 ){
        $auction_current_bid = array();
        $current_value = intval( $auction_starting_price );
    } else {
        $current_bid = $auction_current_bid[count($auction_current_bid)-1];
        $current_value = $current_bid['current_amount'];
    }
    if( is_user_logged_in() ) {
        if (isset($_POST['ipa_bid_updated']) && $_POST['ipa_bid_updated'] == 'true') {
            if (!isset($_POST['ipa_bid_nonce']) || !wp_verify_nonce($_POST['ipa_bid_nonce'], 'ipa_new_bid_nonce')) {
                $ipa_result = esc_html__('Invalid nonce!');
            } else {
                if (
                    isset($_POST['new-bid-value'])
                    && $_POST['new-bid-value'] > $current_value
                ) {
                    $time_now = date("Y-m-d H:i:s");
                    $current_bid_array = array(
                        'current_amount' => intval($_POST['new-bid-value']),
                        'current_user' => intval($current_user_id),
                        'current_timestamp' => $time_now
                    );

                    if ( ! is_array( $auction_bids ) ) {
                        $auction_bids = array();
                    }

                    if ( ! is_array( $auction_bids_email_list ) ) {
                        $auction_bids_email_list = array();
                    }

                    if(
                        isset( $_POST['ipa_bid_emailing_list'] ) &&
                        $_POST['ipa_bid_emailing_list'] == 'true' &&
                        ! in_array( $current_user_email, $auction_bids_email_list )
                    ){
                        $auction_bids_email_list[] = $current_user_email;
                    }

                    $auction_bids_array = array();
                    $auction_bids_array['ipa_current_bid_amount'] = $_POST['new-bid-value'];
                    $auction_bids_array['ipa_bid_owner'] = $current_user->display_name;
                    $auction_bids_array['ipa_bid_time'] = $time_now;
                    $additional_data = esc_html__('Additional Details', 'inspiry-property-auctions') . "\n <br>";

                    if (isset($_POST['ipa_subject_to_finance'])) {
                        $auction_bids_array['ipa_subject_to_finance'] = $_POST['ipa_subject_to_finance'];
                        $additional_data = 'Onder voorbehoud financiering: ' . $_POST['ipa_subject_to_finance'] . "\n <br>";
                    }
                    if (isset($_POST['ipa_building_inspection'])) {
                        $auction_bids_array['ipa_building_inspection'] = $_POST['ipa_building_inspection'];
                        $additional_data .= 'Onder voorbehoud bouwkundige keuring: ' . $_POST['ipa_building_inspection'] . "\n <br>";
                    }
                    if (isset($_POST['ipa_sale_property'])) {
                        $auction_bids_array['ipa_sale_property'] = $_POST['ipa_sale_property'];
                        $additional_data .= 'Onder voorbehoud verkoop eigen woning: ' . $_POST['ipa_sale_property'] . "\n <br>";
                    }
                    if (isset($_POST['ipa_preferred_transfer_date'])) {
                        $auction_bids_array['ipa_preferred_transfer_date'] = $_POST['ipa_preferred_transfer_date'];
                        $additional_data .= 'Voorkeur overdrachtsdatum: ' . $_POST['ipa_preferred_transfer_date'] . "\n <br>";
                    }
                    if (isset($_POST['ipa_going_to_view'])) {
                        $auction_bids_array['ipa_going_to_view'] = $_POST['ipa_going_to_view'];
                        $additional_data .= "Bezichtigd/gaat bezichtigen: " . $_POST['ipa_going_to_view'] . "\n <br>";
                    }
                    if (isset($_POST['ipa_using_own_money'])) {
                        $auction_bids_array['ipa_using_own_money'] = $_POST['ipa_using_own_money'];
                        $additional_data .= "Gebruik je (ook) eigen geld voor de aankoop?: " . $_POST['ipa_using_own_money'] . "\n <br>";
                    }

                    $auction_bids[] = $auction_bids_array;
                    $auction_current_bid[] = $current_bid_array;

                    if (
                        update_post_meta( $post_id, 'ipa_auction_current', $auction_current_bid ) &&
                        update_post_meta( $post_id, 'ipa_auction_bids', $auction_bids ) &&
                        update_post_meta( $post_id, 'ipa_bid_emailing_list', $auction_bids_email_list)
                    ) {
                        $user_to = $current_user_email . ',' . $post_author_email;
                        $user_subject = 'You posted a bid on (' . get_the_title($post_id) . ')';
                        $user_body = esc_html__('Thank you for bidding. We will keep you posted with the status and updates.', 'inspiry-property-auctions');
                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        // email to the current bidder
                        wp_mail($user_to, $user_subject, $user_body, $headers);

                        $author_to = $current_user_email . ',' . $post_author_email;
                        $author_subject = esc_html__("New Bid Alert ", "inspiry-property-auctions") . '(' . get_the_title($post_id) . ')';
                        $author_body = esc_html__("A new bid is added. The details are given below.", "inspiry-property-auctions") . "\n <br>";
                        $author_body .= esc_html__("Property name: ", "inspiry-property-auctions") . get_the_title($post_id) . "\n <br>";
                        $author_body .= esc_html__("Bid Amount: ", "inspiry-property-auctions") . $_POST['new-bid-value'] . "\n <br>";
                        $author_body .= esc_html__("Bid Time: ", "inspiry-property-auctions") . $current_time . "\n <br>";
                        $author_body .= esc_html__("Bidder: ", "inspiry-property-auctions") . $current_user->display_name . PHP_EOL . "\n <br>";
                        $author_body .= $additional_data;
                        // email to the property author
                        wp_mail($author_to, $author_subject, $author_body, $headers);

                        $ex_bidder_subject = esc_html__("New Bid Alter ", "inspiry-property-auctions") . '(' . get_the_title($post_id) . ')';
                        $ex_bidder_body = esc_html__("A new bid is added on a property you was interested in.", "inspiry-property-auctions") . "\n <br>";
                        $ex_bidder_body .= esc_html__("Property URL: ", "inspiry-property-auctions") .
                            '<a href="' . get_permalink( $post_id ) . '">' .
                            get_the_title( $post_id ) .
                            "</a> \n <br>";
                        if ( in_array($current_user_email, $auction_bids_email_list) ) {
                            unset( $auction_bids_email_list[array_search('$current_user_email',$auction_bids_email_list)] );
                        }
                        // email to ex bidders
                        wp_mail($auction_bids_email_list, $ex_bidder_subject, $ex_bidder_body, $headers);
                    }

                } else {
                    $ipa_result = esc_html__('Invalid bid amount', 'inspiry-property-auctions');
                }
            }
        }
    }

    $html = '';

    if( $auction_status && $auction_starting_price > 0 ){
        $html .= '<div class="ipa-auction-wrap">';
            if( ! empty( $auction_title ) ){
                $html .= '<h3>' . esc_html( $auction_title ) . '</h3>';
            } else {
                $html .= '<h3>' . esc_html__( 'Auction Details', 'inspiry-property-auctions' ) . '</h3>';
            }
            if( ! empty( $auction_description ) ){
                $html .= '<p>' . esc_html( $auction_description ) . '</p>';
            }
            $html .= '<div class="current-box">';
                if( ! empty( $auction_starting_price ) ){
                    if( is_array( $auction_current_bid ) && 0 < count( $auction_current_bid ) ){
                        $current_bid = $auction_current_bid[count($auction_current_bid)-1];
                        $current_value = $current_bid['current_amount'];
                        $html .= '<h5>' .
                            esc_html__( 'Current Bid:  ', 'inspiry-property-auctions' ) .
                            ere_format_amount( $current_value ) .
                            '</h5>';
                    } else {
                        $html .= '<h5>' .
                            esc_html__( 'Current Bid:  ', 'inspiry-property-auctions' ) .
                            ere_format_amount( $auction_starting_price ) .
                            '</h5>';
                        $html .= '<h5>' . esc_html__( 'Be the first to bid.', 'inspiry-property-auctions' ) . '</h5>';
                    }

                    $html .= '<h6>' .
                        esc_html__( 'Started At:  ', 'inspiry-property-auctions' ) .
                        ere_format_amount( $auction_starting_price ) .
                        '</h6>';

                    if( ! empty( $auction_end_date ) ){
                        $ending_on = date("d M yy - h:s a", strtotime($auction_end_date));
                        $html .= '<h6>' . esc_html__( 'Auction ending on ' ) . esc_html( $ending_on ) . '</h6>';
                    }
                }
            $html .= '</div>';

        if( is_user_logged_in() ) {
            if (strtotime($current_time) < strtotime($auction_end_date)) {
                $html .= '<form id="auction-' . $post_id . '" action="" method="post" data-current-bid="' . esc_attr($current_value) . '">';
                $html .= '<div class="cbs-wrapper">';
                    $html .= '<div class="cb-wrap">';
                        $html .= '<p>' . esc_html__('Onder voorbehoud financiering', 'inspiry-property-auctions') . '</p>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_subject_to_finance" id="ipa_subject_to_finance_yes" value="yes" checked="checked">';
                        $html .= '<label for="ipa_subject_to_finance_yes">' . esc_html__('Yes', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_subject_to_finance" id="ipa_subject_to_finance_no" value="no">';
                        $html .= '<label for="ipa_subject_to_finance_no">' . esc_html__('No', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                    $html .= '</div>';

                    $html .= '<div class="cb-wrap">';
                        $html .= '<p>' . esc_html__('Onder voorbehoud bouwkundige keuring', 'inspiry-property-auctions') . '</p>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_building_inspection" id="ipa_building_inspection_yes" value="yes" checked="checked">';
                        $html .= '<label for="ipa_building_inspection_yes">' . esc_html__('Yes', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_building_inspection" id="ipa_building_inspection_no" value="no">';
                        $html .= '<label for="ipa_building_inspection_no">' . esc_html__('No', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                    $html .= '</div>';

                    $html .= '<div class="cb-wrap">';
                        $html .= '<p>' . esc_html__('Onder voorbehoud verkoop eigen woning', 'inspiry-property-auctions') . '</p>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_sale_property" id="ipa_sale_property_yes" value="yes" checked="checked">';
                        $html .= '<label for="ipa_sale_property_yes">' . esc_html__('Yes', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_sale_property" id="ipa_sale_property_no" value="no">';
                        $html .= '<label for="ipa_sale_property_no">' . esc_html__('No', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                    $html .= '</div>';

                    $html .= '<div class="cb-wrap">';
                        $html .= '<p>' . esc_html__('Voorkeur overdrachtsdatum', 'inspiry-property-auctions') . '</p>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_preferred_transfer_date" id="ipa_preferred_transfer_date_yes" value="yes" checked="checked">';
                        $html .= '<label for="ipa_preferred_transfer_date_yes">' . esc_html__('Yes', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_preferred_transfer_date" id="ipa_preferred_transfer_date_no" value="no">';
                        $html .= '<label for="ipa_preferred_transfer_date_no">' . esc_html__('No', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                    $html .= '</div>';

                    $html .= '<div class="cb-wrap">';
                        $html .= '<p>' . esc_html__('Bezichtigd/gaat bezichtigen', 'inspiry-property-auctions') . '</p>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_going_to_view" id="ipa_going_to_view_yes" value="yes" checked="checked">';
                        $html .= '<label for="ipa_going_to_view_yes">' . esc_html__('Yes', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_going_to_view" id="ipa_going_to_view_no" value="no">';
                        $html .= '<label for="ipa_going_to_view_no">' . esc_html__('No', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                    $html .= '</div>';

                    $html .= '<div class="cb-wrap">';
                        $html .= '<p>' . esc_html__('Gebruik je (ook) eigen geld voor de aankoop?', 'inspiry-property-auctions') . '</p>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_using_own_money" id="ipa_using_own_money_yes" value="yes" checked="checked">';
                        $html .= '<label for="ipa_using_own_money_yes">' . esc_html__('Yes', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                        $html .= '<span>';
                        $html .= '<input type="radio" name="ipa_using_own_money" id="ipa_using_own_money_no" value="no">';
                        $html .= '<label for="ipa_using_own_money_no">' . esc_html__('No', 'inspiry-property-auctions') . '</label>';
                        $html .= '</span>';
                    $html .= '</div>';

                $html .= '</div>';

                $html .= '<p class="current-mail-list-checkbox">' .
                    '<input type="checkbox" id="add-bid-email-list" name="ipa_bid_emailing_list" value="true" />' .
                    '<label for="add-bid-email-list">' . esc_html__( 'Keep me updated on the future bids', 'inspiry-property-auctions' ) . '</label>' .
                    '</p>';

                $html .= '<div class="bid-field-wrap">';
                $html .= '<input type="number" name="new-bid-value" placeholder="' . esc_html__('Add Amount', 'inspiry-property-auctions') . '">';
                $html .= '<input type="hidden" name="ipa_bid_updated" value="true">';
                $html .= '<input type="hidden" name="current_user_id" value="' . esc_html($current_user_id) . '">';
                $html .= '<input type="hidden" name="ipa_bid_nonce" value="' . wp_create_nonce('ipa_new_bid_nonce') . '">';
                $html .= '<input type="submit" value="' . esc_html__('Bid Now', 'inspiry-property-auctions') . '">';
                $html .= '</div>';
                $html .= '</form>';

                if (!empty($ipa_result)) {
                    $html .= '<p class="ipa-error">' . $ipa_result . '</p>';
                }
            } else {
                $html .= '<p>' . esc_html__('Auction ended!', 'inspiry-property-auctions') . ' </p>';
            }
        } else {
            $html .= '<p class="text-center">' . esc_html__('Login to bid!', 'inspiry-property-auctions') . ' </p>';
        }

        $html .= '</div>';
    }

    $content .= $html;

    return $content;

}

add_filter( 'the_content', 'ipa_auction_display' );