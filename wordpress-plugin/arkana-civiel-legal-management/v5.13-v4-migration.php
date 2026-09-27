<?php
/**
 * V5.13 V4 Migration foundation.
 *
 * This module is intentionally dry-run first. It inventories legacy V4 records
 * and provides a safe migration plan without modifying source records.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V513_V4_Migration' ) ) {
final class ACLM_V513_V4_Migration {
    const META_SOURCE_ID = '_aclm_v4_source_id';
    const META_SOURCE_VERSION = '_aclm_v4_source_version';

    public static function source_post_types() {
        return array( 'ac_client', 'ac_matter', 'ac_legal_request', 'ac_document', 'ac_retainer', 'ac_invoice', 'ac_notification', 'ac_task', 'ac_deadline' );
    }

    public static function inventory() {
        $out = array();
        foreach ( self::source_post_types() as $type ) {
            $q = new WP_Query( array( 'post_type' => $type, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => false ) );
            $out[ $type ] = (int) $q->found_posts;
        }
        return $out;
    }

    public static function already_migrated( $source_id, $target_type ) {
        $q = new WP_Query( array(
            'post_type' => $target_type,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => array(
                array( 'key' => self::META_SOURCE_ID, 'value' => (string) absint( $source_id ) ),
                array( 'key' => self::META_SOURCE_VERSION, 'value' => 'v4' ),
            ),
        ) );
        return ! empty( $q->posts );
    }

    public static function plan( $source_id, $source_type ) {
        $map = array(
            'ac_client' => 'ac_client',
            'ac_matter' => 'ac_matter',
            'ac_legal_request' => 'ac_legal_request',
            'ac_document' => 'ac_document',
            'ac_retainer' => 'ac_retainer',
            'ac_invoice' => 'ac_invoice',
            'ac_notification' => 'ac_notification',
            'ac_task' => 'ac_task',
            'ac_deadline' => 'ac_deadline',
        );
        $source_id = absint( $source_id );
        if ( ! $source_id || empty( $map[ $source_type ] ) ) return array( 'ok' => false, 'reason' => 'Unsupported source record.' );
        $source = get_post( $source_id );
        if ( ! $source || $source->post_type !== $source_type ) return array( 'ok' => false, 'reason' => 'Source record not found.' );
        $target = $map[ $source_type ];
        return array(
            'ok' => true,
            'source_id' => $source_id,
            'source_type' => $source_type,
            'target_type' => $target,
            'title' => $source->post_title,
            'already_migrated' => self::already_migrated( $source_id, $target ),
            'action' => self::already_migrated( $source_id, $target ) ? 'skip' : 'queue',
        );
    }

    public static function dry_run_batch( $source_type, $limit = 25 ) {
        $limit = min( 100, max( 1, absint( $limit ) ) );
        $items = get_posts( array( 'post_type' => $source_type, 'post_status' => 'any', 'numberposts' => $limit, 'orderby' => 'ID', 'order' => 'ASC' ) );
        $plans = array();
        foreach ( $items as $item ) $plans[] = self::plan( $item->ID, $source_type );
        return $plans;
    }
}
}
