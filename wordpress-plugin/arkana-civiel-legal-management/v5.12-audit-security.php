<?php
/**
 * V5.12 Audit & Security hardening foundation.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V512_Audit_Security' ) ) {
final class ACLM_V512_Audit_Security {
    const POST_TYPE = 'ac_audit_event';

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_audit_type' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'meta_box' ) );
        add_filter( 'manage_ac_audit_event_posts_columns', array( __CLASS__, 'columns' ) );
        add_action( 'manage_ac_audit_event_posts_custom_column', array( __CLASS__, 'column_value' ), 10, 2 );
    }

    public static function register_audit_type() {
        register_post_type( self::POST_TYPE, array(
            'labels' => array( 'name' => 'Audit Events', 'singular_name' => 'Audit Event' ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => array( 'title', 'editor' ),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ) );
    }

    public static function meta_box( $post_type ) {
        if ( self::POST_TYPE !== $post_type ) return;
        add_meta_box( 'aclm_v512_audit', 'Audit Event', array( __CLASS__, 'box' ), self::POST_TYPE, 'normal', 'high' );
    }

    public static function box( $post ) {
        $user = (int) get_post_meta( $post->ID, '_aclm_audit_user_id', true );
        $action = get_post_meta( $post->ID, '_aclm_audit_action', true );
        $object = get_post_meta( $post->ID, '_aclm_audit_object', true );
        $ip = get_post_meta( $post->ID, '_aclm_audit_ip_hash', true );
        echo '<p><strong>User ID:</strong> ' . esc_html( $user ) . '</p>';
        echo '<p><strong>Action:</strong> ' . esc_html( $action ) . '</p>';
        echo '<p><strong>Object:</strong> ' . esc_html( $object ) . '</p>';
        echo '<p><strong>IP Hash:</strong> ' . esc_html( $ip ) . '</p>';
        echo '<p><strong>Timestamp:</strong> ' . esc_html( get_post_meta( $post->ID, '_aclm_audit_timestamp', true ) ) . '</p>';
    }

    public static function log( $action, $object_type, $object_id = 0, $meta = array() ) {
        $action = sanitize_key( $action );
        $object_type = sanitize_key( $object_type );
        $object_id = absint( $object_id );
        if ( ! $action || ! $object_type ) return 0;
        $label = sprintf( '%s %s #%d', $action, $object_type, $object_id );
        $id = wp_insert_post( array(
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'post_title' => $label,
            'post_content' => wp_json_encode( self::safe_meta( $meta ) ),
        ), true );
        if ( is_wp_error( $id ) ) return 0;
        update_post_meta( $id, '_aclm_audit_user_id', get_current_user_id() );
        update_post_meta( $id, '_aclm_audit_action', $action );
        update_post_meta( $id, '_aclm_audit_object', $object_type . ':' . $object_id );
        update_post_meta( $id, '_aclm_audit_timestamp', current_time( 'mysql', true ) );
        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) update_post_meta( $id, '_aclm_audit_ip_hash', hash_hmac( 'sha256', sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ), wp_salt( 'auth' ) ) );
        return (int) $id;
    }

    private static function safe_meta( $meta ) {
        if ( ! is_array( $meta ) ) return array();
        $out = array();
        foreach ( $meta as $key => $value ) {
            if ( is_scalar( $value ) ) $out[ sanitize_key( $key ) ] = sanitize_text_field( (string) $value );
        }
        return $out;
    }

    public static function can_access_matter( $matter_id, $user_id = 0 ) {
        $matter_id = absint( $matter_id );
        $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
        if ( ! $matter_id || ! $user_id || ! get_post( $matter_id ) ) return false;
        $user = get_userdata( $user_id );
        if ( ! $user ) return false;
        $roles = (array) $user->roles;
        if ( array_intersect( array( 'administrator', 'ac_managing_partner', 'ac_partner' ), $roles ) ) return true;
        $assignee = (int) get_post_meta( $matter_id, '_aclm_assigned_lawyer', true );
        if ( $assignee === $user_id ) return true;
        $client_id = (int) get_post_meta( $matter_id, '_aclm_client_id', true );
        $user_client = (int) get_user_meta( $user_id, '_aclm_client_id', true );
        return $client_id > 0 && $client_id === $user_client;
    }

    public static function secure_document_download_allowed( $document_id ) {
        $document_id = absint( $document_id );
        if ( ! $document_id || 'ac_document' !== get_post_type( $document_id ) || ! is_user_logged_in() ) return false;
        $matter_id = (int) get_post_meta( $document_id, '_aclm_matter_id', true );
        if ( ! self::can_access_matter( $matter_id ) ) return false;
        $access = get_post_meta( $document_id, '_aclm_document_access', true );
        $user = wp_get_current_user();
        $internal = array_intersect( array( 'administrator', 'ac_managing_partner', 'ac_partner', 'ac_lawyer', 'ac_paralegal' ), (array) $user->roles );
        if ( 'client' === $access && empty( $internal ) ) return true;
        return ! empty( $internal );
    }

    public static function columns( $c ) {
        $c['aclm_audit_action'] = 'Action';
        $c['aclm_audit_object'] = 'Object';
        $c['aclm_audit_user'] = 'User';
        return $c;
    }
    public static function column_value( $col, $id ) {
        if ( 'aclm_audit_action' === $col ) echo esc_html( get_post_meta( $id, '_aclm_audit_action', true ) );
        elseif ( 'aclm_audit_object' === $col ) echo esc_html( get_post_meta( $id, '_aclm_audit_object', true ) );
        elseif ( 'aclm_audit_user' === $col ) echo esc_html( get_post_meta( $id, '_aclm_audit_user_id', true ) );
    }
}
ACLM_V512_Audit_Security::init();
}
