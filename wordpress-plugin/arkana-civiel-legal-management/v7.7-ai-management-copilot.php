<?php
/** V7.7 AI Management Copilot foundation. Read-only management intelligence. */
defined('ABSPATH') || exit;
if(!class_exists('ACLM_V77_AI_Management_Copilot')){
final class ACLM_V77_AI_Management_Copilot {
 public static function init(){add_shortcode('arkana_ai_management_copilot',array(__CLASS__,'shortcode'));}
 private static function allowed(){
  if(!class_exists('ACLM_V70_AI_Agent_Core')||!ACLM_V70_AI_Agent_Core::can_use())return false;
  $u=wp_get_current_user(); return current_user_can('manage_options')||array_intersect(array('ac_managing_partner','ac_partner'),(array)$u->roles);
 }
 public static function get_metrics(){
  if(!self::allowed())return array();
  $metrics=array('matters'=>0,'open_tasks'=>0,'deadlines'=>0,'retainers'=>0,'ai_runs'=>0);
  $types=array('matters'=>'ac_matter','open_tasks'=>'ac_task','deadlines'=>'ac_deadline','retainers'=>'ac_retainer_service','ai_runs'=>'ac_ai_agent_run');
  foreach($types as $key=>$type){if(post_type_exists($type)){$args=array('post_type'=>$type,'post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','no_found_rows'=>false);if($key==='open_tasks'){$args['meta_query']=array(array('key'=>'_aclm_status','value'=>array('completed','closed'),'compare'=>'NOT IN'));}$metrics[$key]=(int)count(get_posts($args));}}
  return apply_filters('aclm_v77_management_metrics',$metrics);
 }
 public static function create_run($prompt,$context=array()){
  if(!self::allowed()||!class_exists('ACLM_V70_AI_Agent_Core'))return 0;
  $context['assistant']='management_copilot';$context['metrics']=self::get_metrics();
  return ACLM_V70_AI_Agent_Core::create_run($prompt,$context);
 }
 public static function shortcode(){if(!self::allowed())return '<p>Akses AI Management Copilot ditolak.</p>';$m=self::get_metrics();ob_start();?><section class="aclm-v77-management-copilot" style="max-width:1100px;margin:40px auto;padding:0 20px"><h1>AI Management Copilot</h1><p>Read-only management intelligence for authorized Managing Partners and Partners.</p><ul><li>Matters: <?php echo esc_html($m['matters']);?></li><li>Open Tasks: <?php echo esc_html($m['open_tasks']);?></li><li>Deadlines: <?php echo esc_html($m['deadlines']);?></li><li>Retainers: <?php echo esc_html($m['retainers']);?></li><li>AI Runs: <?php echo esc_html($m['ai_runs']);?></li></ul></section><?php return ob_get_clean();}
}
ACLM_V77_AI_Management_Copilot::init();
}
