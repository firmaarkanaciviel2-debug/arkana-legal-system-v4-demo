<?php
/** V7.6 AI Legal Knowledge Base foundation. Provider-neutral, permission-aware retrieval registry. */
defined('ABSPATH') || exit;
if(!class_exists('ACLM_V76_AI_Legal_Knowledge_Base')){
final class ACLM_V76_AI_Legal_Knowledge_Base {
 const CPT='ac_legal_knowledge';
 public static function init(){add_action('init',array(__CLASS__,'register'));add_shortcode('arkana_ai_knowledge_base',array(__CLASS__,'shortcode'));}
 public static function register(){register_post_type(self::CPT,array('labels'=>array('name'=>'Legal Knowledge','singular_name'=>'Legal Knowledge Item'),'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'supports'=>array('title','editor'),'capability_type'=>'post','map_meta_cap'=>true));}
 private static function allowed(){return class_exists('ACLM_V70_AI_Agent_Core') && ACLM_V70_AI_Agent_Core::can_use();}
 public static function search($query,$limit=10){
  if(!self::allowed())return array(); $query=sanitize_text_field($query);$limit=max(1,min(25,absint($limit)));if(!$query)return array();
  $posts=get_posts(array('post_type'=>self::CPT,'post_status'=>'publish','posts_per_page'=>$limit,'s'=>$query,'orderby'=>'relevance'));
  return array_map(function($p){return array('id'=>(int)$p->ID,'title'=>get_the_title($p->ID),'excerpt'=>wp_strip_all_tags(wp_trim_words($p->post_content,45)),'modified'=>get_post_modified_time('c',true,$p->ID),'source_type'=>sanitize_text_field(get_post_meta($p->ID,'_aclm_source_type',true)),'source_ref'=>sanitize_text_field(get_post_meta($p->ID,'_aclm_source_ref',true)));},$posts);
 }
 public static function create_run($query,$context=array()){
  if(!self::allowed())return 0;$results=self::search($query,10);$context['assistant']='legal_knowledge';$context['retrieval_results']=$results;
  return class_exists('ACLM_V70_AI_Agent_Core')?ACLM_V70_AI_Agent_Core::create_run($query,$context):0;
 }
 public static function shortcode($atts){$atts=shortcode_atts(array('query'=>''),$atts,'arkana_ai_knowledge_base');if(!self::allowed())return '<p>Akses AI Legal Knowledge Base ditolak.</p>';$q=sanitize_text_field($atts['query']);$r=$q?self::search($q,10):array();ob_start();?><section class="aclm-v76-knowledge-base" style="max-width:1100px;margin:40px auto;padding:0 20px"><h1>AI Legal Knowledge Base</h1><p>Provider-neutral, permission-aware legal knowledge retrieval foundation.</p><?php if($q):?><h2>Results for: <?php echo esc_html($q);?></h2><ul><?php foreach($r as $item):?><li><strong><?php echo esc_html($item['title']);?></strong> — <?php echo esc_html($item['excerpt']);?></li><?php endforeach;?></ul><?php endif;?></section><?php return ob_get_clean();}
}
ACLM_V76_AI_Legal_Knowledge_Base::init();
}
