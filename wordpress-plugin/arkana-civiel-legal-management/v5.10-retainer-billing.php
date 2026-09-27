<?php
/**
 * V5.10 Retainer & Billing foundation.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V510_Retainer_Billing' ) ) {
final class ACLM_V510_Retainer_Billing {
    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
        add_action( 'save_post_ac_retainer', array( __CLASS__, 'save_retainer' ), 10, 2 );
        add_action( 'save_post_ac_invoice', array( __CLASS__, 'save_invoice' ), 10, 2 );
        add_filter( 'manage_ac_retainer_posts_columns', array( __CLASS__, 'retainer_columns' ) );
        add_action( 'manage_ac_retainer_posts_custom_column', array( __CLASS__, 'retainer_column_value' ), 10, 2 );
        add_filter( 'manage_ac_invoice_posts_columns', array( __CLASS__, 'invoice_columns' ) );
        add_action( 'manage_ac_invoice_posts_custom_column', array( __CLASS__, 'invoice_column_value' ), 10, 2 );
    }

    public static function meta_boxes() {
        add_meta_box( 'aclm_v510_retainer', 'Arkana Civiel — Retainer V5.10', array( __CLASS__, 'retainer_box' ), 'ac_retainer', 'normal', 'high' );
        add_meta_box( 'aclm_v510_invoice', 'Arkana Civiel — Invoice V5.10', array( __CLASS__, 'invoice_box' ), 'ac_invoice', 'normal', 'high' );
    }

    private static function select( $name, $value, $options ) {
        echo '<select name="' . esc_attr( $name ) . '" style="min-width:260px;">';
        foreach ( $options as $key => $label ) echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
        echo '</select>';
    }

    private static function clients() {
        return get_posts( array( 'post_type' => 'ac_client', 'post_status' => 'publish', 'numberposts' => 100, 'orderby' => 'title', 'order' => 'ASC' ) );
    }

    public static function retainer_box( $post ) {
        wp_nonce_field( 'aclm_v510_retainer', 'aclm_v510_retainer_nonce' );
        $client = (int) get_post_meta( $post->ID, '_aclm_client_id', true );
        $start = get_post_meta( $post->ID, '_aclm_start_date', true );
        $end = get_post_meta( $post->ID, '_aclm_end_date', true );
        $fee = get_post_meta( $post->ID, '_aclm_retainer_fee', true );
        $currency = get_post_meta( $post->ID, '_aclm_currency', true ) ?: 'IDR';
        $status = get_post_meta( $post->ID, '_aclm_retainer_status', true ) ?: 'draft';
        $billing = get_post_meta( $post->ID, '_aclm_billing_cycle', true ) ?: 'monthly';
        ?>
        <p><strong>Client</strong><br><select name="_aclm_client_id" style="min-width:320px;"><option value="0">— Pilih Client —</option><?php foreach ( self::clients() as $c ) : ?><option value="<?php echo esc_attr( $c->ID ); ?>" <?php selected( $client, $c->ID ); ?>><?php echo esc_html( $c->post_title ); ?></option><?php endforeach; ?></select></p>
        <p><strong>Start Date</strong><br><input type="date" name="_aclm_start_date" value="<?php echo esc_attr( $start ); ?>" /></p>
        <p><strong>End Date</strong><br><input type="date" name="_aclm_end_date" value="<?php echo esc_attr( $end ); ?>" /></p>
        <p><strong>Fee</strong><br><input type="number" name="_aclm_retainer_fee" value="<?php echo esc_attr( $fee ); ?>" min="0" step="0.01" /></p>
        <p><strong>Currency</strong><br><?php self::select( '_aclm_currency', $currency, array( 'IDR' => 'IDR', 'USD' => 'USD', 'SGD' => 'SGD' ) ); ?></p>
        <p><strong>Billing Cycle</strong><br><?php self::select( '_aclm_billing_cycle', $billing, array( 'monthly'=>'Monthly', 'quarterly'=>'Quarterly', 'annual'=>'Annual', 'custom'=>'Custom' ) ); ?></p>
        <p><strong>Status</strong><br><?php self::select( '_aclm_retainer_status', $status, array( 'draft'=>'Draft', 'active'=>'Active', 'suspended'=>'Suspended', 'expired'=>'Expired', 'terminated'=>'Terminated' ) ); ?></p>
        <?php
    }

    public static function invoice_box( $post ) {
        wp_nonce_field( 'aclm_v510_invoice', 'aclm_v510_invoice_nonce' );
        $client = (int) get_post_meta( $post->ID, '_aclm_client_id', true );
        $retainer = (int) get_post_meta( $post->ID, '_aclm_retainer_id', true );
        $amount = get_post_meta( $post->ID, '_aclm_invoice_amount', true );
        $due = get_post_meta( $post->ID, '_aclm_invoice_due_date', true );
        $status = get_post_meta( $post->ID, '_aclm_invoice_status', true ) ?: 'draft';
        ?>
        <p><strong>Client</strong><br><select name="_aclm_client_id" style="min-width:320px;"><option value="0">— Pilih Client —</option><?php foreach ( self::clients() as $c ) : ?><option value="<?php echo esc_attr( $c->ID ); ?>" <?php selected( $client, $c->ID ); ?>><?php echo esc_html( $c->post_title ); ?></option><?php endforeach; ?></select></p>
        <p><strong>Retainer ID (opsional)</strong><br><input type="number" name="_aclm_retainer_id" value="<?php echo esc_attr( $retainer ); ?>" min="0" /></p>
        <p><strong>Amount</strong><br><input type="number" name="_aclm_invoice_amount" value="<?php echo esc_attr( $amount ); ?>" min="0" step="0.01" /></p>
        <p><strong>Due Date</strong><br><input type="date" name="_aclm_invoice_due_date" value="<?php echo esc_attr( $due ); ?>" /></p>
        <p><strong>Status</strong><br><?php self::select( '_aclm_invoice_status', $status, array( 'draft'=>'Draft', 'issued'=>'Issued', 'partially_paid'=>'Partially Paid', 'paid'=>'Paid', 'overdue'=>'Overdue', 'void'=>'Void' ) ); ?></p>
        <?php
    }

    private static function valid_date( $value ) { return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ); }

    public static function save_retainer( $post_id, $post ) {
        if ( empty( $_POST['aclm_v510_retainer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aclm_v510_retainer_nonce'] ) ), 'aclm_v510_retainer' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        if ( isset( $_POST['_aclm_client_id'] ) ) update_post_meta( $post_id, '_aclm_client_id', absint( $_POST['_aclm_client_id'] ) );
        if ( isset( $_POST['_aclm_retainer_fee'] ) ) update_post_meta( $post_id, '_aclm_retainer_fee', max( 0, (float) $_POST['_aclm_retainer_fee'] ) );
        foreach ( array( '_aclm_start_date', '_aclm_end_date' ) as $key ) if ( isset( $_POST[$key] ) ) { $v = sanitize_text_field( wp_unslash( $_POST[$key] ) ); if ( self::valid_date( $v ) ) update_post_meta( $post_id, $key, $v ); }
        foreach ( array( '_aclm_currency'=>array('IDR','USD','SGD'), '_aclm_billing_cycle'=>array('monthly','quarterly','annual','custom'), '_aclm_retainer_status'=>array('draft','active','suspended','expired','terminated') ) as $key=>$allowed ) if ( isset( $_POST[$key] ) ) { $v=sanitize_key(wp_unslash($_POST[$key])); if(in_array($v,$allowed,true)) update_post_meta($post_id,$key,$v); }
    }

    public static function save_invoice( $post_id, $post ) {
        if ( empty( $_POST['aclm_v510_invoice_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aclm_v510_invoice_nonce'] ) ), 'aclm_v510_invoice' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        foreach ( array( '_aclm_client_id','_aclm_retainer_id' ) as $key ) if(isset($_POST[$key])) update_post_meta($post_id,$key,absint($_POST[$key]));
        if(isset($_POST['_aclm_invoice_amount'])) update_post_meta($post_id,'_aclm_invoice_amount',max(0,(float)$_POST['_aclm_invoice_amount']));
        if(isset($_POST['_aclm_invoice_due_date'])) {$v=sanitize_text_field(wp_unslash($_POST['_aclm_invoice_due_date']));if(self::valid_date($v))update_post_meta($post_id,'_aclm_invoice_due_date',$v);}
        if(isset($_POST['_aclm_invoice_status'])){$v=sanitize_key(wp_unslash($_POST['_aclm_invoice_status']));if(in_array($v,array('draft','issued','partially_paid','paid','overdue','void'),true))update_post_meta($post_id,'_aclm_invoice_status',$v);}
    }

    public static function retainer_columns($c){$c['aclm_client']='Client';$c['aclm_status']='Status';$c['aclm_fee']='Fee';$c['aclm_period']='Period';return $c;}
    public static function retainer_column_value($col,$id){if('aclm_client'===$col){$c=(int)get_post_meta($id,'_aclm_client_id',true);echo $c?esc_html(get_the_title($c)):'—';}elseif('aclm_status'===$col)echo esc_html(get_post_meta($id,'_aclm_retainer_status',true)?:'—');elseif('aclm_fee'===$col)echo esc_html(get_post_meta($id,'_aclm_retainer_fee',true)?:'0');elseif('aclm_period'===$col)echo esc_html((get_post_meta($id,'_aclm_start_date',true)?:'—').' → '.(get_post_meta($id,'_aclm_end_date',true)?:'—'));}
    public static function invoice_columns($c){$c['aclm_client']='Client';$c['aclm_amount']='Amount';$c['aclm_due']='Due';$c['aclm_status']='Status';return $c;}
    public static function invoice_column_value($col,$id){if('aclm_client'===$col){$c=(int)get_post_meta($id,'_aclm_client_id',true);echo $c?esc_html(get_the_title($c)):'—';}elseif('aclm_amount'===$col)echo esc_html(get_post_meta($id,'_aclm_invoice_amount',true)?:'0');elseif('aclm_due'===$col)echo esc_html(get_post_meta($id,'_aclm_invoice_due_date',true)?:'—');elseif('aclm_status'===$col)echo esc_html(get_post_meta($id,'_aclm_invoice_status',true)?:'—');}
}
ACLM_V510_Retainer_Billing::init();
}
