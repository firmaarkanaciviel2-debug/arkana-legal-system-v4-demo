<?php
/** V6.1 Client 360 foundation. */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'ACLM_V61_Client_360' ) ) {
final class ACLM_V61_Client_360 {
    public static function init() { add_shortcode( 'arkana_client_360', array( __CLASS__, 'shortcode' ) ); }
    private static function count_for_client( $type, $client_id, $meta_key = '_aclm_client_id' ) {
        if ( ! $client_id || ! post_type_exists( $type ) ) return 0;
        $q = new WP_Query( array( 'post_type'=>$type, 'post_status'=>'publish', 'posts_per_page'=>1, 'fields'=>'ids', 'meta_query'=>array( array('key'=>$meta_key,'value'=>(string)$client_id,'compare'=>'=') ) ) );
        return (int)$q->found_posts;
    }
    public static function profile( $client_id ) {
        $client_id = absint( $client_id );
        if ( ! $client_id || 'ac_client' !== get_post_type($client_id) ) return array();
        return array(
            'id'=>$client_id,
            'name'=>get_the_title($client_id),
            'matters'=>self::count_for_client('ac_matter',$client_id),
            'legal_requests'=>self::count_for_client('ac_legal_request',$client_id),
            'retainers'=>self::count_for_client('ac_retainer',$client_id),
            'invoices'=>self::count_for_client('ac_invoice',$client_id),
        );
    }
    public static function shortcode( $atts ) {
        if ( ! is_user_logged_in() ) return '<p>Silakan login untuk mengakses Client 360.</p>';
        $atts = shortcode_atts(array('client_id'=>0),$atts,'arkana_client_360');
        $client_id = absint($atts['client_id']);
        if ( ! $client_id ) $client_id = (int)get_user_meta(get_current_user_id(),'_aclm_client_id',true);
        if ( ! $client_id ) return '<p>Client belum ditentukan.</p>';
        if ( class_exists('ACLM_V512_Audit_Security') && ! ACLM_V512_Audit_Security::can_access_matter( self::first_matter($client_id) ) ) {
            $u=wp_get_current_user();
            if ( ! array_intersect(array('administrator','ac_managing_partner','ac_partner'),(array)$u->roles) ) return '<p>Akses Client 360 ditolak.</p>';
        }
        $p=self::profile($client_id); if(!$p) return '<p>Client tidak ditemukan.</p>';
        ob_start(); ?>
        <section class="aclm-v61-client360" style="max-width:1100px;margin:40px auto;padding:0 20px;">
        <h1>Client 360 — <?php echo esc_html($p['name']); ?></h1>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;">
        <?php foreach(array('matters'=>'Matters','legal_requests'=>'Legal Requests','retainers'=>'Retainers','invoices'=>'Invoices') as $k=>$label): ?>
        <div style="border:1px solid #dfe5ea;border-radius:10px;padding:18px;background:#fff;"><small><?php echo esc_html($label); ?></small><div style="font-size:28px;font-weight:700;"><?php echo esc_html($p[$k]); ?></div></div>
        <?php endforeach; ?></div></section><?php return ob_get_clean();
    }
    private static function first_matter($client_id){
        $q=new WP_Query(array('post_type'=>'ac_matter','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','meta_query'=>array(array('key'=>'_aclm_client_id','value'=>(string)$client_id,'compare'=>'='))));
        return $q->posts ? (int)$q->posts[0] : 0;
    }
}
ACLM_V61_Client_360::init();
}
