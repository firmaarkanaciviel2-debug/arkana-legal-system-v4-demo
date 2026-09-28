<?php
/**
 * V7.15 — Retainer & Client Portfolio Intelligence.
 *
 * Read-only analytics contract. It deliberately does not mutate client,
 * matter, retainer, invoice, or task records.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class ACLM_V715_Portfolio_Intelligence {

    public static function analyze( array $snapshot ): array {
        $clients   = isset( $snapshot['clients'] ) && is_array( $snapshot['clients'] ) ? $snapshot['clients'] : [];
        $retainers = isset( $snapshot['retainers'] ) && is_array( $snapshot['retainers'] ) ? $snapshot['retainers'] : [];

        $result = [
            'clients_total'          => count( $clients ),
            'active_clients'         => 0,
            'retainers_total'       => count( $retainers ),
            'active_retainers'      => 0,
            'attention_required'    => 0,
            'portfolio_health'      => 'unknown',
            'signals'               => [],
            'client_portfolio'      => [],
            'retainer_portfolio'    => [],
        ];

        foreach ( $clients as $client ) {
            $status = strtolower( (string) ( $client['status'] ?? 'active' ) );
            if ( in_array( $status, [ 'active', 'open', 'current' ], true ) ) {
                $result['active_clients']++;
            }
        }

        foreach ( $retainers as $retainer ) {
            $status = strtolower( (string) ( $retainer['status'] ?? 'active' ) );
            $health = strtolower( (string) ( $retainer['health'] ?? 'normal' ) );
            if ( in_array( $status, [ 'active', 'current' ], true ) ) {
                $result['active_retainers']++;
            }
            if ( in_array( $health, [ 'critical', 'warning', 'at_risk', 'at-risk' ], true ) ) {
                $result['attention_required']++;
            }
        }

        if ( $result['attention_required'] > 0 ) {
            $result['portfolio_health'] = 'attention';
            $result['signals'][] = 'One or more retainers require management attention.';
        } elseif ( $result['active_retainers'] > 0 || $result['active_clients'] > 0 ) {
            $result['portfolio_health'] = 'stable';
        } else {
            $result['portfolio_health'] = 'empty';
        }

        $result['client_portfolio']   = $clients;
        $result['retainer_portfolio'] = $retainers;

        return $result;
    }
}

/**
 * Integration hook. A canonical data provider can supply clients/retainers
 * without changing the analytics layer.
 */
add_filter( 'aclm_v715_portfolio_snapshot', static function ( $snapshot ) {
    return is_array( $snapshot ) ? ACLM_V715_Portfolio_Intelligence::analyze( $snapshot ) : [];
} );
