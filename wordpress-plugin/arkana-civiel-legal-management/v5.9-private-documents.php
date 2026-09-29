<?php
/**
 * V5.9 Private Document Management foundation.
 *
 * Files are stored outside the public WordPress uploads directory when a
 * custom private root is configured. This foundation deliberately does not
 * expose direct attachment URLs to clients.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V59_Private_Documents' ) ) {
final class ACLM_V59_Private_Documents {
    const META_MATTER = '_aclm_matter_id';
    const META_CLIENT = '_aclm_client_id';
    const META_ACCESS = '_aclm_document_access';

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
        add_action( 'save_post_ac_document', array( __CLASS__, 'save_meta' ), 10, 2 );
        add_filter( 'manage_ac_document_posts_columns', array( __CLASS__, 'columns' ) );
        add_action( 'manage_ac_document_posts_custom_column', array( __CLASS__, 'column_value' ), 10, 2 );
    }

    public static function meta_boxes() {
        add_meta_box( 'aclm_v59_document', 'Arkana Civiel — Private Document V5.9', array( __CLASS__, 'box' ), 'ac_document', 'normal', 'high' );
    }

    public static function box( $post ) {
        wp_nonce_field( 'aclm_v59_document', 'aclm_v59_nonce' );
        $matter = (int) get_post_meta( $post->ID, self::META_MATTER, true );
        $client = (int) get_post_meta( $post->ID, self::META_CLIENT, true );
        $access = get_post_meta( $post->ID, self::META_ACCESS, true ) ?: 'internal';
        $matters = get_posts( array( 'post_type' => 'ac_matter', 'post_status' => 'publish', 'numberposts' => 100, 'orderby' => 'title', 'order' => 'ASC' ) );
        ?>
        <p><strong>Matter</strong><br><select name="_aclm_matter_id" style="min-width:320px;"><option value="0">— Pilih Matter —</option><?php foreach ( $matters as $m ) : ?><option value="<?php echo esc_attr( $m->ID ); ?>" <?php selected( $matter, $m->ID ); ?>><?php echo esc_html( $m->post_title ); ?></option><?php endforeach; ?></select></p>
        <p><strong>Client ID</strong><br><input type="number" name="_aclm_client_id" value="<?php echo esc_attr( $client ); ?>" min="0" /></p>
        <p><strong>Access Level</strong><br><select name="_aclm_document_access"><option value="internal" <?php selected( $access, 'internal' ); ?>>Internal Only</option><option value="client" <?php selected( $access, 'client' ); ?>>Client Visible</option></select></p>
        <p style="color:#64748b;">V5.9 tidak menggunakan direct public URL sebagai mekanisme otorisasi. Delivery file melalui authenticated endpoint akan ditambahkan pada security hardening V5.12.</p>
        <?php
    }

    public static function save_meta( $post_id, $post ) {
        if ( empty( $_POST['aclm_v59_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aclm_v59_nonce'] ) ), 'aclm_v59_document' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        if ( isset( $_POST['_aclm_matter_id'] ) ) update_post_meta( $post_id, self::META_MATTER, absint( $_POST['_aclm_matter_id'] ) );
        if ( isset( $_POST['_aclm_client_id'] ) ) update_post_meta( $post_id, self::META_CLIENT, absint( $_POST['_aclm_client_id'] ) );
        if ( isset( $_POST['_aclm_document_access'] ) ) {
            $access = sanitize_key( wp_unslash( $_POST['_aclm_document_access'] ) );
            if ( in_array( $access, array( 'internal', 'client' ), true ) ) update_post_meta( $post_id, self::META_ACCESS, $access );
        }
    }

    public static function columns( $columns ) {
        $columns['aclm_doc_matter'] = 'Matter';
        $columns['aclm_doc_client'] = 'Client ID';
        $columns['aclm_doc_access'] = 'Access';
        return $columns;
    }

    public static function column_value( $column, $post_id ) {
        if ( 'aclm_doc_matter' === $column ) {
            $id = (int) get_post_meta( $post_id, self::META_MATTER, true );
            echo $id ? esc_html( get_the_title( $id ) ) : '—';
        } elseif ( 'aclm_doc_client' === $column ) {
            echo esc_html( (int) get_post_meta( $post_id, self::META_CLIENT, true ) ?: '—' );
        } elseif ( 'aclm_doc_access' === $column ) {
            echo esc_html( get_post_meta( $post_id, self::META_ACCESS, true ) ?: 'internal' );
        }
    }

    /** Return a private-root proposal for future file delivery. */
    public static function private_root() {
        $configured = defined( 'ACLM_PRIVATE_DOCUMENT_ROOT' ) ? ACLM_PRIVATE_DOCUMENT_ROOT : '';
        if ( $configured ) return trailingslashit( wp_normalize_path( $configured ) );
        return trailingslashit( WP_CONTENT_DIR ) . 'aclm-private-documents/';
    }

    public static function is_private_access_level( $document_id ) {
        return 'internal' === get_post_meta( $document_id, self::META_ACCESS, true );
    }
}
ACLM_V59_Private_Documents::init();
}
