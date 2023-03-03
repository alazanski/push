<?php
/*
Plugin Name: Push: Your data layer buddy
Plugin URI: http://www.alazanski.com
Description: Push useful events such as product view, add to cart and purchase events to the data layer JavaScript. Use this lightweight plugin when setting up conversion tracking using Google Tag Manager.
Version: 1.0
Author: alazanski
Author URI: http://www.example.com
License: MIT
*/

// Add to Cart Event
function add_to_cart_event() {
    global $woocommerce;
    $product_id = get_the_ID();
    $product = wc_get_product($product_id);
    ?>
    <script>
        dataLayer.push({
            'event': 'addToCart',
            'ecommerce': {
                'currencyCode': '<?php echo get_woocommerce_currency(); ?>',
                'add': {
                    'products': [{
                        'name': '<?php echo $product->get_name(); ?>',
                        'id': '<?php echo $product_id; ?>',
                        'price': '<?php echo $product->get_price(); ?>',
                        'quantity': '1'
                    }]
                }
            }
        });
    </script>
    <?php
}
add_action( 'wp_footer', 'add_to_cart_event', 10 );

// Purchase Event
function purchase_event( $order_id ) {
    $order = wc_get_order( $order_id );
    $order_data = $order->get_data();
    $order_items = $order->get_items();
    $products = array();
    foreach( $order_items as $item ) {
        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);
        $products[] = array(
            'name' => $product->get_name(),
            'id' => $product_id,
            'price' => $product->get_price(),
            'quantity' => $item->get_quantity()
        );
    }
    ?>
    <script>
        dataLayer.push({
            'event': 'purchase',
            'ecommerce': {
                'currencyCode': '<?php echo $order_data['currency']; ?>',
                'purchase': {
                    'actionField': {
                        'id': '<?php echo $order_data['id']; ?>',
                        'affiliation': '<?php echo $order_data['payment_method_title']; ?>',
                        'revenue': '<?php echo $order_data['total']; ?>',
                        'tax': '<?php echo $order_data['total_tax']; ?>',
                        'shipping': '<?php echo $order_data['shipping_total']; ?>',
                        'coupon': '<?php echo $order_data['coupon_code']; ?>'
                    },
                    'products': <?php echo json_encode($products); ?>
                }
            }
        });
    </script>
    <?php
}
add_action( 'woocommerce_thankyou', 'purchase_event', 10, 1 );
