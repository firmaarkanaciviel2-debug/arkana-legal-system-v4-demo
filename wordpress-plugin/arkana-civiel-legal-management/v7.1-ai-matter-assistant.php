<?php
/** V7.1 AI Matter Assistant foundation. */
defined('ABSPATH') || exit;
if(!class_exists('ACLM_V71_AI_Matter_Assistant')){
final class ACLM_V71_AI_Matter_Assistant {
 public static function init(){add_shortcode('arkana_ai_matter_assistant',array(__CLASS__,'shortcode'));}
 private static function allowed($matter_id){
  if(!class_exists('ACLM_V70_AI_Agent_Core')) return false;
  return ACLM_V70_AI_Agent_Core::can_use() && ACLM_V70_AI_Agent_Core::can_access_matter($matter_id);
 }
 public static function get_context($matter_id){
  $matter_id=absint($matter_id); if(!$matter_id || 'ac_matter'!==get_post_type($matter_id) || !self::allowed($matter_id)) return array();
  $context=array('matter_id'=>$matter_id,'title'=>get_the_title($matter_id),'status'=>sanitize_text_field(get_post_meta($matter_id,'_aclm_status',true)),'client_id'=>(int)get_post_meta($matter_id,'_aclm_client_id',true));
  if(post_type_exists('ac_deadline')) $context['deadlines']=self::related('ac_deadline',$matter_id,'_aclm_matter_id');
  if(post_type_exists('ac_task')) $context['tasks']=self::related('ac_task',$matter_id,'_aclm_matter_id');
  if(post_type_exists('ac_litigation_event')) $context['litigation_events']=self::related('ac_litigation_event',$matter_id,'_aclm_matter_id');
  return apply_filters('aclm_v71_matter_context',$context,$matter_id);
 }
 private static function related($type,$matter_id,$key){
  $posts=get_posts(array('post_type'=>$type,'post_status'=>'publish','numberposts'=>25,'meta_key'=>$key,'meta_value'=>(string)$matter_id,'orderby'=>'date','order'=>'DESC'));
  return array_map(function($p){return array('id'=>(int)$p->ID,'title'=>get_the_title($p->ID),'date'=>get_the_date('c',$p));},$posts);
 }
 public static function create_run($matter_id,$prompt){
  if(!self::allowed($matter_id)) return 0;
  $context=self::get_context($matter_id); if(!$context) return 0;
  if(class_exists('ACLM_V70_AI_Agent_Core')) return ACLM_V70_AI_Agent_Core::create_run($prompt,array('matter_id'=>absint($matter_id),'assistant'=>'matter','context'=>$context));
  return 0;
 }
 public static function shortcode($atts){
  $atts=shortcode_atts(array('matter_id'=>0),$atts,'arkana_ai_matter_assistant'); $id=absint($atts['matter_id']);
  if(!$id || !self::allowed($id)) return '<p>Akses AI Matter Assistant ditolak.</p>';
  $c=self::get_context($id); ob_start(); ?><section class="aclm-v71-matter-assistant" style="max-width:1100px;margin:40px auto;padding:0 20px"><h1>AI Matter Assistant — <?php echo esc_html($c['title']); ?></h1><p>Read-only Matter context is available to the authorized Agent. No legal action or record mutation is executed by V7.1.</p><ul><li>Status: <?php echo esc_html($c['status'] ?: '—'); ?></li><li>Tasks found: <?php echo esc_html(count($c['tasks']??array())); ?></li><li>Deadlines found: <?php echo esc_html(count($c['deadlines']??array())); ?></li><li>Litigation events found: <?php echo esc_html(count($c['litigation_events']??array())); ?></li></ul></section><?php return ob_get_clean();
 }
}
ACLM_V71_AI_Matter_Assistant::init();
}
