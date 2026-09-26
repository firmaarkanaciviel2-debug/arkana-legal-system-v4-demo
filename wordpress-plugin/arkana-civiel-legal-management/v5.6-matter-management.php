<?php
/**
 * V5.6 Matter Management foundation.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V56_Matter_Management' ) ) {
final class ACLM_V56_Matter_Management {
    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
        add_action( 'save_post_ac_matter', array( __CLASS__, 'save_meta' ), 10, 2 );
        add_filter( 'manage_ac_matter_posts_columns', array( __CLASS__, 'columns' ) );
        add_action( 'manage_ac_matter_posts_custom_column', array( __CLASS__, 'column_value' ), 10, 2 );
    }

    public static function meta_boxes() {
        add_meta_box( 'aclm_v56_matter', 'Arkana Civiel — Matter Management V5.6', array( __CLASS__, 'box' ), 'ac_matter', 'normal', 'high' );
    }

    private static function select( $name, $value, $options ) {
        echo '<select name="' . esc_attr( $name ) . '" style="min-width:260px;">';
        foreach ( $options as $key => $label ) {
            echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    public static function box( $post ) {
        wp_nonce_field( 'aclm_v56_save_matter', 'aclm_v56_nonce' );
        $client = (int) get_post_meta( $post->ID, '_aclm_client_id', true );
        $partner = (int) get_post_meta( $post->ID, '_aclm_partner_id', true );
        $lawyer = (int) get_post_meta( $post->ID, '_aclm_lead_lawyer', true );
        $status = get_post_meta( $post->ID, '_aclm_status', true ) ?: 'active';
        $priority = get_post_meta( $post->ID, '_aclm_priority', true ) ?: 'normal';
        $progress = (int) get_post_meta( $post->ID, '_aclm_progress', true );
        $stage = get_post_meta( $post->ID, '_aclm_stage', true ) ?: 'assessment';
        $client_posts = get_posts( array( 'post_type' => 'ac_client', 'post_status' => 'publish', 'numberposts' => 100, 'orderby' => 'title', 'order' => 'ASC' ) );
        $users = get_users( array( 'role__in' => array( 'ac_partner', 'ac_managing_partner', 'ac_lawyer' ), 'orderby' => 'display_name', 'order' => 'ASC' ) );
        ?>
        <p><label><strong>Client</strong></label><br />
        <select name="_aclm_client_id" style="min-width:320px;"><option value="0">— Pilih Client —</option><?php foreach ( $client_posts as $c ) : ?><option value="<?php echo esc_attr( $c->ID ); ?>" <?php selected( $client, $c->ID ); ?>><?php echo esc_html( $c->post_title ); ?></option><?php endforeach; ?></select></p>
        <p><label><strong>Partner Penanggung Jawab</strong></label><br /><?php self::select_user( '_aclm_partner_id', $partner, $users ); ?></p>
        <p><label><strong>Lead Lawyer</strong></label><br /><?php self::select_user( '_aclm_lead_lawyer', $lawyer, $users ); ?></p>
        <p><label><strong>Status</strong></label><br /><?php self::select( '_aclm_status', $status, array( 'active'=>'Active', 'on_hold'=>'On Hold', 'closed'=>'Closed', 'archived'=>'Archived' ) ); ?></p>
        <p><label><strong>Priority</strong></label><br /><?php self::select( '_aclm_priority', $priority, array( 'low'=>'Low', 'normal'=>'Normal', 'high'=>'High', 'critical'=>'Critical' ) ); ?></p>
        <p><label><strong>Stage</strong></label><br /><?php self::select( '_aclm_stage', $stage, array( 'assessment'=>'Assessment', 'engagement'=>'Engagement', 'strategy'=>'Strategy', 'execution'=>'Execution', 'resolution'=>'Resolution', 'closed'=>'Closed' ) ); ?></p>
        <p><label><strong>Progress (%)</strong></label><br /><input type="number" name="_aclm_progress" value="<?php echo esc_attr( max( 0, min( 100, $progress ) ) ); ?>" min="0" max="100" step="1" style="width:120px;" /></p>
        <p style="color:#64748b;">V5.6 menyiapkan portfolio perkara dan progress monitoring. Authorization per Matter dan private document access tetap mengikuti security layer V5.9/V5.12.</p>
        <?php
    }

    private static function select_user( $name, $value, $users ) {
        echo '<select name="' . esc_attr( $name ) . '" style="min-width:320px;"><option value="0">— Tidak ditetapkan —</option>';
        foreach ( $users as $u ) {
            echo '<option value="' . esc_attr( $u->ID ) . '" ' . selected( $value, $u->ID, false ) . '>' . esc_html( $u->display_name . ' (' . implode( ', ', $u->roles ) . ')' ) . '</option>';
        }
        echo '</select>';
    }

    public static function save_meta( $post_id, $post ) {
        if ( ! isset( $_POST['aclm_v56_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aclm_v56_nonce'] ) ), 'aclm_v56_save_matter' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        $fields = array( '_aclm_client_id' => 'absint', '_aclm_partner_id' => 'absint', '_aclm_lead_lawyer' => 'absint', '_aclm_progress' => 'absint' );
        foreach ( $fields as $key => $type ) {
            if ( isset( $_POST[ $key ] ) ) update_post_meta( $post_id, $key, absint( $_POST[ $key ] ) );
        }
        $enums = array(
            '_aclm_status' => array( 'active', 'on_hold', 'closed', 'archived' ),
            '_aclm_priority' => array( 'low', 'normal', 'high', 'critical' ),
            '_aclm_stage' => array( 'assessment', 'engagement', 'strategy', 'execution', 'resolution', 'closed' ),
        );
        foreach ( $enums as $key => $allowed ) {
            if ( isset( $_POST[ $key ] ) ) {
                $value = sanitize_key( wp_unslash( $_POST[ $key ] ) );
                if ( in_array( $value, $allowed, true ) ) update_post_meta( $post_id, $key, $value );
            }
        }
    }

    public static function columns( $columns ) {
        $columns['aclm_client'] = 'Client';
        $columns['aclm_status'] = 'Status';
        $columns['aclm_priority'] = 'Priority';
        $columns['aclm_progress'] = 'Progress';
        return $columns;
    }

    public static function column_value( $column, $post_id ) {
        if ( 'aclm_client' === $column ) {
            $id = (int) get_post_meta( $post_id, '_aclm_client_id', true );
            echo $id ? esc_html( get_the_title( $id ) ) : '—';
        } elseif ( 'aclm_status' === $column ) {
            echo esc_html( get_post_meta( $post_id, '_aclm_status', true ) ?: '—' );
        } elseif ( 'aclm_priority' === $column ) {
            echo esc_html( get_post_meta( $post_id, '_aclm_priority', true ) ?: '—' );
        } elseif ( 'aclm_progress' === $column ) {
            echo esc_html( (int) get_post_meta( $post_id, '_aclm_progress', true ) ) . '%';
        }
    }
}
ACLM_V56_Matter_Management::init();
}
