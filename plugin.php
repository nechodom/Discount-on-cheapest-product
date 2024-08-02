<?php
/*
Plugin Name: Discount for 3 Products
Description: Provides a discount for the cheapest product when a specified number of products are in the cart.
Version: 1.2
Author: Matěj Kevin Nechodom
Author URI: https://www.nechodom.cz
*/

// Přidání menu do administrace
function dp_register_settings() {
    add_option( 'dp_enable_discount', '1' );
    add_option( 'dp_discount_percentage', '100' );
    add_option( 'dp_minimum_products', '3' );
    register_setting( 'dp_options_group', 'dp_enable_discount', 'dp_callback' );
    register_setting( 'dp_options_group', 'dp_discount_percentage', 'intval' );
    register_setting( 'dp_options_group', 'dp_minimum_products', 'intval' );
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
    <tr valign="top">
    <th scope="row"><label for="dp_discount_percentage">Discount Percentage</label></th>
    <td><input type="number" id="dp_discount_percentage" name="dp_discount_percentage" value="<?php echo get_option( 'dp_discount_percentage' ); ?>" /></td>
    </tr>
    <tr valign="top">
    <th scope="row"><label for="dp_minimum_products">Minimum Products in Cart</label></th>
    <td><input type="number" id="dp_minimum_products" name="dp_minimum_products" value="<?php echo get_option( 'dp_minimum_products' ); ?>" /></td>
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

    $minimum_products = get_option( 'dp_minimum_products' );

    // Zkontrolujte, zda je v košíku alespoň nastavený počet produktů
    if ( WC()->cart->get_cart_contents_count() < $minimum_products ) {
        return;
    }

    $discount_percentage = get_option( 'dp_discount_percentage' );

    $cart = WC()->cart->get_cart();
    $product_prices = array();

    // Získejte ceny všech produktů v košíku
    foreach ( $cart as $cart_item_key => $cart_item ) {
        for ($i = 0; $i < $cart_item['quantity']; $i++) {
            $product_prices[] = $cart_item['line_total'] / $cart_item['quantity'];
        }
    }

    // Seřaďte ceny od nejnižší po nejvyšší
    sort( $product_prices );

    // Získejte nejnižší cenu
    $lowest_price = $product_prices[0];

    // Vypočítejte slevu na základě procenta
    $discount_amount = ($lowest_price * $discount_percentage) / 100;

    // Přidejte slevu odpovídající nejnižší ceně
    WC()->cart->add_fee( 'Sleva na nejlevnější produkt', -$discount_amount );
}
add_action( 'woocommerce_cart_calculate_fees', 'custom_woocommerce_cart_discount' );
