<?php
/**
 * V5.7 Legal Request workflow foundation.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V57_Legal_Request' ) ) {
final class ACLM_V57_Legal_Request {
    const NONCE = 'aclm_v57_request';

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
        add_action( 'save_post_ac_request', array( __CLASS__, 'save_meta' ), 10, 2 );
        add_filter( 'manage_ac_request_posts_columns', array( __CLASS__, 'columns' ) );
        add_action( 'manage_ac_request_posts_custom_column', array( __CLASS__, 'column_value' ), 10, 2 );
        add_shortcode( 'arkana_legal_request_form', array( __CLASS__, 'form' ) );
        add_action( 'init', array( __CLASS__, 'handle_submission' ) );
    }

    public static function meta_boxes() {
        add_meta_box( 'aclm_v57_request', 'Arkana Civiel — Legal Request V5.7', array( __CLASS__, 'box' ), 'ac_request', 'normal', 'high' );
    }

    public static function box( $post ) {
        wp_nonce_field( 'aclm_v57_save', 'aclm_v57_nonce' );
        $client = (int) get_post_meta( $post->ID, '_aclm_client_id', true );
        $matter = (int) get_post_meta( $post->ID, '_aclm_matter_id', true );
        $status = get_post_meta( $post->ID, '_aclm_request_status', true ) ?: 'new';
        $priority = get_post_meta( $post->ID, '_aclm_request_priority', true ) ?: 'normal';
        $type = get_post_meta( $post->ID, '_aclm_request_type', true ) ?: 'consultation';
        ?>
        <p><strong>Client ID</strong><br><input type="number" name="_aclm_client_id" value="<?php echo esc_attr( $client ); ?>" min="0" /></p>
        <p><strong>Matter ID</strong><br><input type="number" name="_aclm_matter_id" value="<?php echo esc_attr( $matter ); ?>" min="0" /></p>
        <p><strong>Type</strong><br><?php self::select( '_aclm_request_type', $type, array( 'consultation'=>'Consultation', 'contract_review'=>'Contract Review', 'corporate'=>'Corporate', 'litigation'=>'Litigation', 'employment'=>'Employment', 'other'=>'Other' ) ); ?></p>
        <p><strong>Status</strong><br><?php self::select( '_aclm_request_status', $status, array( 'new'=>'New', 'triaged'=>'Triaged', 'assigned'=>'Assigned', 'in_progress'=>'In Progress', 'waiting_client'=>'Waiting Client', 'resolved'=>'Resolved', 'closed'=>'Closed', 'rejected'=>'Rejected' ) ); ?></p>
        <p><strong>Priority</strong><br><?php self::select( '_aclm_request_priority', $priority, array( 'low'=>'Low', 'normal'=>'Normal', 'high'=>'High', 'urgent'=>'Urgent' ) ); ?></p>
        <p style="color:#64748b;">V5.7 memisahkan intake permintaan hukum dari Matter. Relasi dan authorization granular akan diperketat pada security layer berikutnya.</p>
        <?php
    }

    private static function select( $name, $value, $options ) {
        echo '<select name="' . esc_attr( $name ) . '">';
        foreach ( $options as $key => $label ) echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
        echo '</select>';
    }

    public static function save_meta( $post_id, $post ) {
        if ( ! isset( $_POST['aclm_v57_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aclm_v57_nonce'] ) ), 'aclm_v57_save' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        foreach ( array( '_aclm_client_id', '_aclm_matter_id' ) as $key ) if ( isset( $_POST[ $key ] ) ) update_post_meta( $post_id, $key, absint( $_POST[ $key ] ) );
        $enums = array(
            '_aclm_request_type' => array( 'consultation', 'contract_review', 'corporate', 'litigation', 'employment', 'other' ),
            '_aclm_request_status' => array( 'new', 'triaged', 'assigned', 'in_progress', 'waiting_client', 'resolved', 'closed', 'rejected' ),
            '_aclm_request_priority' => array( 'low', 'normal', 'high', 'urgent' ),
        );
        foreach ( $enums as $key => $allowed ) {
            if ( isset( $_POST[ $key ] ) ) {
                $value = sanitize_key( wp_unslash( $_POST[ $key ] ) );
                if ( in_array( $value, $allowed, true ) ) update_post_meta( $post_id, $key, $value );
            }
        }
    }

    public static function columns( $columns ) {
        $columns['aclm_request_type'] = 'Type';
        $columns['aclm_request_status'] = 'Status';
        $columns['aclm_request_priority'] = 'Priority';
        return $columns;
    }

    public static function column_value( $column, $post_id ) {
        $map = array( 'aclm_request_type' => '_aclm_request_type', 'aclm_request_status' => '_aclm_request_status', 'aclm_request_priority' => '_aclm_request_priority' );
        if ( isset( $map[ $column ] ) ) echo esc_html( get_post_meta( $post_id, $map[ $column ], true ) ?: '—' );
    }

    public static function form() {
        if ( ! is_user_logged_in() ) return '<p>Silakan login untuk mengajukan permintaan hukum.</p>';
        $user = wp_get_current_user();
        if ( ! in_array( 'ac_client_admin', (array) $user->roles, true ) && ! in_array( 'ac_client_user', (array) $user->roles, true ) ) return '<p>Form ini hanya tersedia untuk akun klien.</p>';
        ob_start(); ?>
        <form method="post" class="aclm-legal-request-form">
            <p><label>Judul Permintaan<br><input required type="text" name="aclm_request_title" maxlength="160" /></label></p>
            <p><label>Jenis Permintaan<br><select name="aclm_request_type"><option value="consultation">Consultation</option><option value="contract_review">Contract Review</option><option value="corporate">Corporate</option><option value="litigation">Litigation</option><option value="employment">Employment</option><option value="other">Other</option></select></label></p>
            <p><label>Deskripsi<br><textarea required name="aclm_request_description" rows="6" maxlength="5000"></textarea></label></p>
            <?php wp_nonce_field( self::NONCE, 'aclm_request_nonce' ); ?>
            <input type="hidden" name="aclm_submit_request" value="1" />
            <button type="submit">Kirim Permintaan Hukum</button>
        </form>
        <?php return ob_get_clean();
    }

    public static function handle_submission() {
        if ( empty( $_POST['aclm_submit_request'] ) || ! is_user_logged_in() ) return;
        if ( empty( $_POST['aclm_request_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aclm_request_nonce'] ) ), self::NONCE ) ) return;
        $user = wp_get_current_user();
        if ( ! in_array( 'ac_client_admin', (array) $user->roles, true ) && ! in_array( 'ac_client_user', (array) $user->roles, true ) ) return;
        $client_id = (int) get_user_meta( $user->ID, '_aclm_client_id', true );
        if ( ! $client_id ) return;
        $title = sanitize_text_field( wp_unslash( $_POST['aclm_request_title'] ?? '' ) );
        $description = sanitize_textarea_field( wp_unslash( $_POST['aclm_request_description'] ?? '' ) );
        if ( '' === $title || '' === $description ) return;
        $allowed = array( 'consultation', 'contract_review', 'corporate', 'litigation', 'employment', 'other' );
        $type = sanitize_key( wp_unslash( $_POST['aclm_request_type'] ?? 'other' ) );
        if ( ! in_array( $type, $allowed, true ) ) $type = 'other';
        $id = wp_insert_post( array( 'post_type' => 'ac_request', 'post_status' => 'publish', 'post_title' => $title, 'post_content' => $description, 'post_author' => $user->ID ), true );
        if ( is_wp_error( $id ) ) return;
        update_post_meta( $id, '_aclm_client_id', $client_id );
        update_post_meta( $id, '_aclm_request_type', $type );
        update_post_meta( $id, '_aclm_request_status', 'new' );
        update_post_meta( $id, '_aclm_request_priority', 'normal' );
    }
}
ACLM_V57_Legal_Request::init();
}
