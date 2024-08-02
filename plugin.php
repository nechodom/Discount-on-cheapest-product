<?php
/*
Plugin Name: Discount for 3 Products
Description: Provides a 100% discount for the cheapest product. Minimal is 3 products in the cart.
Version: 1.0
Author: Matěj Kevin Nechodom
*/

// Přidání menu do administrace
function dp_register_settings() {
    add_option( 'dp_enable_discount', '1' );
    register_setting( 'dp_options_group', 'dp_enable_discount', 'dp_callback' );
}
add_action( 'admin_init', 'dp_register_settings' );

function dp_register_options_page() {
    add_options_page('Discount Plugin Settings', 'Discount Settings', 'manage_options', 'dp_plugin', 'dp_options_page');
}
add_action('admin_menu', 'dp_register_options_page');

function dp_options_page() {
?>
    <div>
    <h2>Discount Plugin Settings</h2>
    <form method="post" action="options.php">
    <?php settings_fields( 'dp_options_group' ); ?>
    <table>
    <tr valign="top">
    <th scope="row"><label for="dp_enable_discount">Enable Discount</label></th>
    <td><input type="checkbox" id="dp_enable_discount" name="dp_enable_discount" value="1" <?php checked( 1, get_option( 'dp_enable_discount' ), true ); ?> /></td>
    </tr>
    </table>
    <?php  submit_button(); ?>
    </form>
    </div>
<?php
}

// Funkce pro aplikaci slevy
function custom_woocommerce_cart_discount() {
    if ( get_option( 'dp_enable_discount' ) != '1' ) {
        return;
    }

    // Zkontrolujte, zda je v košíku alespoň 3 produkty
    if ( WC()->cart->get_cart_contents_count() < 3 ) {
        return;
    }

    $cart = WC()->cart->get_cart();
    $product_prices = array();

    // Získejte ceny všech produktů v košíku
    foreach ( $cart as $cart_item_key => $cart_item ) {
        $product_prices[] = $cart_item['line_total'];
    }

    // Seřaďte ceny od nejnižší po nejvyšší
    sort( $product_prices );

    // Získejte nejnižší cenu
    $lowest_price = $product_prices[0];

    // Přidejte slevu odpovídající nejnižší ceně
    WC()->cart->add_fee( 'Zdarma nejlevnější produkt', -$lowest_price );
}
add_action( 'woocommerce_cart_calculate_fees', 'custom_woocommerce_cart_discount' );
