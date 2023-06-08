<div class="form-field form-field-wide">
    <?php
    $is_subscribed = get_post_meta( $order->get_id(), 'mailchimp_woocommerce_is_subscribed', true );
    $cbvalue = $is_subscribed ? 'yes' : 'no';
    ?>
    <div class="mailchimp-woocommerce-subscribe">
        <?php
        woocommerce_wp_checkbox( array(
            'id' => 'mailchimp_woocommerce_is_subscribed',
            'label' => 'Subscribe user in Mailchimp?',
            'value' => $cbvalue,
            'style' => 'width: auto; margin-left: 10px;',
        ) );
        ?>
    </div>
</div>