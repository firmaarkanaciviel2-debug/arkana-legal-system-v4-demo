<?php
/** V6.8 Legal Reporting foundation. */
defined('ABSPATH') || exit;
if(!class_exists('ACLM_V68_Reporting')){
final class ACLM_V68_Reporting {
 public static function init(){add_shortcode('arkana_reports_360',array(__CLASS__,'shortcode'));}
 private static function count($type,$meta=array()){
  if(!post_type_exists($type)) return 0;
  $a=array('post_type'=>$type,'post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids'); if($meta)$a['meta_query']=$meta;
  return (int)(new WP_Query($a))->found_posts;
 }
 public static function summary(){
  $data=array(
   'clients'=>self::count('ac_client'),
   'matters'=>self::count('ac_matter'),
   'open_workflows'=>0,
   'active_retainers'=>self::count('ac_retainer_service',array(array('key'=>'_aclm_retainer_status','value'=>'active','compare'=>'='))),
   'tasks'=>self::count('ac_task'),
   'deadlines'=>self::count('ac_deadline'),
   'litigation_events'=>self::count('ac_litigation_event'),
  );
  if(post_type_exists('ac_workflow_instance')) $data['open_workflows']=self::count('ac_workflow_instance',array(array('key'=>'_aclm_workflow_state','value'=>'closed','compare'=>'!=')));
  return apply_filters('aclm_v68_report_summary',$data);
 }
 public static function shortcode(){
  if(!is_user_logged_in())return '<p>Silakan login untuk mengakses Reporting.</p>';
  $u=wp_get_current_user(); if(!current_user_can('manage_options')&&!array_intersect(array('ac_managing_partner','ac_partner'),(array)$u->roles))return '<p>Akses Reporting ditolak.</p>';
  $s=self::summary(); ob_start(); ?>
  <section class="aclm-v68-reporting" style="max-width:1100px;margin:40px auto;padding:0 20px"><h1>Arkana Civiel — Management Report</h1><p>Operational summary</p><table style="width:100%;border-collapse:collapse"><thead><tr><th style="text-align:left;padding:10px;border-bottom:1px solid #ddd">Metric</th><th style="text-align:right;padding:10px;border-bottom:1px solid #ddd">Value</th></tr></thead><tbody><?php foreach($s as $k=>$v): ?><tr><td style="padding:10px;border-bottom:1px solid #eee"><?php echo esc_html(ucwords(str_replace('_',' ',$k))); ?></td><td style="padding:10px;text-align:right;border-bottom:1px solid #eee"><?php echo esc_html($v); ?></td></tr><?php endforeach; ?></tbody></table></section><?php return ob_get_clean();
 }
}
ACLM_V68_Reporting::init();
}
