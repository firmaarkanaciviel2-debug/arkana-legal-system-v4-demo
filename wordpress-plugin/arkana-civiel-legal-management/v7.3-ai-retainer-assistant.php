<?php
/** V7.3 AI Retainer Assistant foundation. */
defined('ABSPATH') || exit;
if(!class_exists('ACLM_V73_AI_Retainer_Assistant')){
final class ACLM_V73_AI_Retainer_Assistant {
 public static function init(){add_shortcode('arkana_ai_retainer_assistant',array(__CLASS__,'shortcode'));}
 private static function allowed($retainer_id){
  if(!class_exists('ACLM_V70_AI_Agent_Core') || !ACLM_V70_AI_Agent_Core::can_use()) return false;
  return current_user_can('manage_options') || in_array('ac_managing_partner',(array)wp_get_current_user()->roles,true) || in_array('ac_partner',(array)wp_get_current_user()->roles,true) || in_array('ac_lawyer',(array)wp_get_current_user()->roles,true);
 }
 public static function get_context($retainer_id){
  $retainer_id=absint($retainer_id); if(!$retainer_id || !post_type_exists('ac_retainer_service') || 'ac_retainer_service'!==get_post_type($retainer_id) || !self::allowed($retainer_id)) return array();
  $matters=self::related('ac_matter',$retainer_id,array('_aclm_retainer_id','_aclm_retainer_service_id'));
  $tasks=self::related('ac_task',$retainer_id,array('_aclm_retainer_id','_aclm_retainer_service_id'));
  $deadlines=self::related('ac_deadline',$retainer_id,array('_aclm_retainer_id','_aclm_retainer_service_id'));
  return apply_filters('aclm_v73_retainer_context',array('retainer_id'=>$retainer_id,'title'=>get_the_title($retainer_id),'status'=>sanitize_text_field(get_post_meta($retainer_id,'_aclm_retainer_status',true)),'matters'=>$matters,'tasks'=>$tasks,'deadlines'=>$deadlines),$retainer_id);
 }
 private static function related($type,$id,$keys){
  if(!post_type_exists($type)) return array(); $ids=array();
  foreach($keys as $key){$posts=get_posts(array('post_type'=>$type,'post_status'=>'publish','numberposts'=>50,'meta_key'=>$key,'meta_value'=>(string)$id,'orderby'=>'date','order'=>'DESC')); foreach($posts as $p)$ids[$p->ID]=$p;}
  return array_map(function($p){return array('id'=>(int)$p->ID,'title'=>get_the_title($p->ID),'date'=>get_the_date('c',$p),'status'=>sanitize_text_field(get_post_meta($p->ID,'_aclm_status',true)));},array_values($ids));
 }
 public static function create_run($retainer_id,$prompt){
  $context=self::get_context($retainer_id); if(!$context || !class_exists('ACLM_V70_AI_Agent_Core')) return 0;
  return ACLM_V70_AI_Agent_Core::create_run($prompt,array('retainer_id'=>absint($retainer_id),'assistant'=>'retainer','context'=>$context));
 }
 public static function shortcode($atts){
  $atts=shortcode_atts(array('retainer_id'=>0),$atts,'arkana_ai_retainer_assistant'); $id=absint($atts['retainer_id']);
  if(!$id || !self::allowed($id)) return '<p>Akses AI Retainer Assistant ditolak.</p>';
  $c=self::get_context($id); ob_start(); ?><section class="aclm-v73-retainer-assistant" style="max-width:1100px;margin:40px auto;padding:0 20px"><h1>AI Retainer Assistant — <?php echo esc_html($c['title']); ?></h1><p>Read-only retainer context is available to the authorized Agent. V7.3 does not change contracts, billing, Matter records, or client communications.</p><ul><li>Status: <?php echo esc_html($c['status'] ?: '—'); ?></li><li>Related Matters: <?php echo esc_html(count($c['matters'])); ?></li><li>Tasks: <?php echo esc_html(count($c['tasks'])); ?></li><li>Deadlines: <?php echo esc_html(count($c['deadlines'])); ?></li></ul></section><?php return ob_get_clean();
 }
}
ACLM_V73_AI_Retainer_Assistant::init();
}
