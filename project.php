<?php
/**
 * Plugin Name: Wimtorq
 * Description:   Kareem Ibrahim Test Task
 * * Author: Kareem Ibrahim
 * Author URI: https://www.linkedin.com/in/ibrahim-kareem-node/
 */
require_once('stripe-php/init.php');


add_action('admin_menu', 'add_admin_menu');

function add_admin_menu(){
    add_menu_page('Wimtorq', 'Wimtorq', 'manage_options', 'wimtorq-menu', 'render_page', 'dashicons-chart-pie', 2);

}

function render_page() {
    ?>
    <form action="options.php" method="post">
        <?php 
         settings_fields( 'my-plugin' );
         do_settings_sections( 'plugin_section' );
         ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}

function plugin_register_settings() {
    register_setting( 'my-plugin', 'stripe-api', 'input_validate');
    add_settings_section( 'api_settings', 'API Settings', 'plugin_section_text', 'plugin_section' );
    
    add_settings_field( 'api-key', 'API KEY', 'dbi_plugin_setting_api_key', 'plugin_section', 'api_settings' );

    add_settings_field( 'client-id', 'Client-ID', 'client_id_callback', 'plugin_section', 'api_settings' );
    
}
add_action( 'admin_init', 'plugin_register_settings' );

function input_validate( $input ) {
    $newinput['api_key'] = trim( $input['api_key'] );
    if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['api_key'] ) ) {
        $newinput['api_key'] = $input['api_key'];
    }

    return $input;
}

function plugin_section_text() {
    echo '<p>Enter Your Stripe API Details</p>';
}

function dbi_plugin_setting_api_key() {
    $options = get_option( 'stripe-api' );
    echo "<input id='api-key' name='stripe-api[api_key]' type='text' value='" . esc_attr($options['api_key']) . "' />";
}

function client_id_callback() {
    $options = get_option( 'stripe-api' );
    echo "<input id='client-id'' name='stripe-api[client_id]' type='text' value='" . esc_attr( $options['client_id'] ) . "' />";
}
add_shortcode('wimtorq-stripe', 'wimtorq_fn');
function wimtorq_fn() {
    $options = get_option( 'stripe-api' );
    $key = $options['client_id'];
    ob_start();
    ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css"/>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
          
    <input id="data" type="hidden" value ="<?php echo $key; ?>">   
    <table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>product</th>
                <th>unit_amount</th>
            </tr>
        </thead>
    </table>
    <script>
    $(document).ready(function () {
        const data = document.querySelector('#data');
        const key = data.value;
        let values = [];
         $.ajax({
            method: 'POST',
            url: '/cache/wp-content/plugins/wimtorq/app.php',
            data: {data: key},
            success: function(response){
                let data = JSON.parse(response);
                data = data.data;
                let arr = [];
                for(let response of data){
                    arr.push(response.product);
                    arr.push(response.unit_amount);
                    values.push(arr);
                    arr = [];
                }
            $('#example').DataTable({
             data: values
         });
            },
             error: function(xhr, status, error) {
            console.log(error);
             },
            });
});
    </script>
    <?php
    return ob_get_clean();
}