<?php
/** V6.7 Legal Operations Analytics foundation. */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'ACLM_V67_Analytics' ) ) {
final class ACLM_V67_Analytics {
    public static function init() { add_shortcode('arkana_analytics_360',array(__CLASS__,'shortcode')); }
    private static function count($type,$meta=array()) {
        if(!post_type_exists($type)) return 0;
        $args=array('post_type'=>$type,'post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids');
        if($meta) $args['meta_query']=$meta;
        $q=new WP_Query($args); return (int)$q->found_posts;
    }
    public static function metrics(){
        $metrics=array(
            'active_clients'=>self::count('ac_client',array(array('key'=>'_aclm_status','value'=>'active','compare'=>'='))),
            'matters'=>self::count('ac_matter'),
            'litigation_events'=>self::count('ac_litigation_event'),
            'active_retainers'=>self::count('ac_retainer_service',array(array('key'=>'_aclm_retainer_status','value'=>'active','compare'=>'='))),
            'open_workflows'=>0,
            'communications'=>self::count('ac_communication'),
            'tasks'=>self::count('ac_task'),
            'deadlines'=>self::count('ac_deadline'),
        );
        if(post_type_exists('ac_workflow_instance')) {
            $q=new WP_Query(array('post_type'=>'ac_workflow_instance','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','meta_query'=>array(array('key'=>'_aclm_workflow_state','value'=>'closed','compare'=>'!='))));
            $metrics['open_workflows']=(int)$q->found_posts;
        }
        return apply_filters('aclm_v67_analytics_metrics',$metrics);
    }
    public static function shortcode(){
        if(!is_user_logged_in()) return '<p>Silakan login untuk mengakses Analytics.</p>';
        if(!current_user_can('manage_options') && !in_array('ac_managing_partner',(array)wp_get_current_user()->roles,true) && !in_array('ac_partner',(array)wp_get_current_user()->roles,true)) return '<p>Akses Analytics ditolak.</p>';
        $m=self::metrics(); ob_start(); ?><section class="aclm-v67-analytics" style="max-width:1100px;margin:40px auto;padding:0 20px;"><h1>Legal Operations Analytics</h1><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;"><?php foreach($m as $key=>$value): ?><div style="border:1px solid #dfe5ea;border-radius:10px;padding:18px;background:#fff;"><small><?php echo esc_html(ucwords(str_replace('_',' ',$key))); ?></small><div style="font-size:28px;font-weight:700;"><?php echo esc_html($value); ?></div></div><?php endforeach; ?></div></section><?php return ob_get_clean();
    }
}
ACLM_V67_Analytics::init();
}
