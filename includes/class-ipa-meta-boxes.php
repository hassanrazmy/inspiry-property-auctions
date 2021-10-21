<?php
/**
 * Class ERE_Meta_Boxes
 *
 * Class to handle stuff related to meta boxes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class IPA_Meta_Boxes {

    public function ipa_additional_meta_boxes( $metabox ){

        foreach( $metabox as $k => $meta ){
            if( isset( $meta[ 'id' ] ) &&
                $meta[ 'id' ] == 'property-meta-box'
            ){
                $tabs = $meta[ 'tabs' ];
                $fields = $meta[ 'fields' ];

                $tabs['auctions'] = array(
                    'label' => esc_html__( 'Property Auction', 'inspiry-property-auctions' ),
                    'icon'  => 'fas fa-gavel',
                );

                $fields[] = array(
                    'id' => "ipa_auction_status",
                    'name' => esc_html__( 'Auction', 'inspiry-property-auctions' ),
                    'type' => 'switch',
                    'std' => '',
                    'tab' => 'auctions'
                );

                $fields[] = array(
                    'id' => "ipa_auction_title",
                    'name' => esc_html__( 'Auction title', 'inspiry-property-auctions' ),
                    'type' => 'text',
                    'std' => '',
                    'tab' => 'auctions'
                );

                $fields[] = array(
                    'id' => "ipa_auction_description",
                    'name' => esc_html__( 'Auction Description', 'inspiry-property-auctions' ),
                    'type' => 'textarea',
                    'std' => '',
                    'tab' => 'auctions'
                );

                $fields[] = array(
                    'id' => "ipa_auction_start_price",
                    'name' => esc_html__( 'Starting Price', 'inspiry-property-auctions' ),
                    'type' => 'number',
                    'std' => '',
                    'columns' => 6,
                    'tab' => 'auctions'
                );

                $fields[] = array(
                    'id' => "ipa_auction_end_date",
                    'name' => esc_html__( 'End Date', 'inspiry-property-auctions' ),
                    'type' => 'datetime',
                    'std' => '',
                    'js_options' => array(
                        'dateFormat'      => 'dd M yy -',
                        'showTimepicker'  => true,
                    ),
                    'save_format' => 'Y-m-d H:i:s',
                    'columns' => 6,
                    'tab' => 'auctions'
                );

                $metabox[ $k ][ 'tabs' ] = $tabs;
                $metabox[ $k ][ 'fields' ] = $fields;
            }
        }
        return $metabox;

    }

    public function ipa_auction_bids_meta_boxes(){
        add_meta_box(
            'ipa_auction_bids_list_meta',
            esc_html__( 'Auction Bids List', 'inspiry-property-auctions' ),
            array( $this, 'ipa_auction_bids_custom_box_html' ),
            'property'
        );
    }

    public function ipa_auction_bids_custom_box_html( $post ) {
        $post_id = $post->ID;
        if(
                isset( $_GET['reset_all_bids'] ) &&
                $_GET['reset_all_bids'] == 'true' &&
                isset( $_GET['post'] ) &&
                $_GET['post'] > 0
        ){
            if(
                    update_post_meta( $_GET['post'], 'ipa_auction_bids', '' ) &&
                    update_post_meta( $_GET['post'], 'ipa_auction_current', '' )
            ){
                ?>
                <p><?php esc_html_e( 'The bids are removed for a fresh start.', 'inspiry-property-auctions' ); ?></p>
                <?php
            }
        } else {
            $auction_bids_array = get_post_meta( $post_id, 'ipa_auction_bids', true );
            if( is_array( $auction_bids_array ) ){
                $auction_bids_array = array_reverse( $auction_bids_array );
                ?>
                <p class="reset-button-wrap">
                    <a href="post.php?post=<?php echo esc_attr( $post_id ); ?>&action=edit&reset_all_bids=true"><?php esc_html_e( 'Reset All Bids', 'inspiry-property-auctions' ); ?></a>
                </p>
                <table>
                    <tr>
                        <th><?php esc_html_e( 'Bid Amount', 'inspiry-property-auctions' ); ?></th>
                        <th><?php esc_html_e( 'Bid User', 'inspiry-property-auctions' ); ?></th>
                        <th><?php esc_html_e( 'Date & Time', 'inspiry-property-auctions' ); ?></th>
                        <th><?php esc_html_e( 'Other Details', 'inspiry-property-auctions' ); ?></th>
                    </tr>
                    <?php
                    foreach( $auction_bids_array as $bid ){
                        ?>
                        <tr>
                            <td><?php echo esc_html( $bid['ipa_current_bid_amount'] ); ?></td>
                            <td><?php echo esc_html( $bid['ipa_bid_owner'] ); ?></td>
                            <td><?php echo esc_html( $bid['ipa_bid_time'] ); ?></td>
                            <td>
                                <?php
                                echo esc_html__( 'Onder voorbehoud financiering: ', 'inspiry-property-auctions' ) .
                                    '<strong>' .
                                    esc_html( $bid['ipa_subject_to_finance'] ) . '</strong><br>';
                                echo esc_html__( 'Onder voorbehoud bouwkundige keuring: ', 'inspiry-property-auctions' ) .
                                    '<strong>' .
                                    esc_html( $bid['ipa_building_inspection'] ) . '</strong><br>';
                                echo esc_html__( 'Onder voorbehoud verkoop eigen woning: ', 'inspiry-property-auctions' ) .
                                    '<strong>' .
                                    esc_html( $bid['ipa_sale_property'] ) . '</strong><br>';
                                echo esc_html__( 'Voorkeur overdrachtsdatum: ', 'inspiry-property-auctions' ) .
                                    '<strong>' .
                                    esc_html( $bid['ipa_preferred_transfer_date'] ) . '</strong><br>';
                                echo esc_html__( 'Bezichtigd/gaat bezichtigen: ', 'inspiry-property-auctions' ) .
                                    '<strong>' .
                                    esc_html( $bid['ipa_going_to_view'] ) . '</strong><br>';
                                echo esc_html__( 'Gebruik je (ook) eigen geld voor de aankoop?: ', 'inspiry-property-auctions' ) .
                                    '<strong>' .
                                    esc_html( $bid['ipa_using_own_money'] ) . '</strong><br>';
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
                <?php
            }
        }
    }

}