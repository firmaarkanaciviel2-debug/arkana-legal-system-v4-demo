<?php
/** V7.5 AI Document Intelligence foundation. Metadata extraction only; no external AI provider yet. */
defined('ABSPATH') || exit;
if(!class_exists('ACLM_V75_AI_Document_Intelligence')){
final class ACLM_V75_AI_Document_Intelligence {
 public static function init(){add_shortcode('arkana_ai_document_intelligence',array(__CLASS__,'shortcode'));}
 private static function allowed(){return class_exists('ACLM_V70_AI_Agent_Core') && ACLM_V70_AI_Agent_Core::can_use();}
 public static function get_document_context($document_id){
  $document_id=absint($document_id); if(!$document_id || !self::allowed())return array();
  $type=get_post_type($document_id); if(!$type)return array();
  $matter_id=(int)get_post_meta($document_id,'_aclm_matter_id',true);
  if($matter_id && !ACLM_V70_AI_Agent_Core::can_access_matter($matter_id))return array();
  return apply_filters('aclm_v75_document_context',array('document_id'=>$document_id,'title'=>get_the_title($document_id),'post_type'=>$type,'matter_id'=>$matter_id,'mime_type'=>sanitize_text_field(get_post_mime_type($document_id) ?: ''),'modified'=>get_post_modified_time('c',true,$document_id)), $document_id);
 }
 public static function create_run($document_id,$prompt){
  $context=self::get_document_context($document_id); if(!$context || !class_exists('ACLM_V70_AI_Agent_Core'))return 0;
  return ACLM_V70_AI_Agent_Core::create_run($prompt,array('document_id'=>absint($document_id),'assistant'=>'document','context'=>$context));
 }
 public static function shortcode($atts){
  $atts=shortcode_atts(array('document_id'=>0),$atts,'arkana_ai_document_intelligence'); $id=absint($atts['document_id']);
  if(!$id || !self::allowed())return '<p>Akses AI Document Intelligence ditolak.</p>';
  $c=self::get_document_context($id); if(!$c)return '<p>Dokumen tidak tersedia atau tidak diotorisasi.</p>';
  ob_start();?><section class="aclm-v75-document-intelligence" style="max-width:1100px;margin:40px auto;padding:0 20px"><h1>AI Document Intelligence — <?php echo esc_html($c['title']);?></h1><p>Document metadata is available to the authorized Agent. V7.5 currently does not upload document contents to an AI provider or generate legal conclusions.</p><ul><li>Type: <?php echo esc_html($c['post_type']);?></li><li>MIME: <?php echo esc_html($c['mime_type'] ?: '—');?></li><li>Matter ID: <?php echo esc_html($c['matter_id'] ?: '—');?></li><li>Modified: <?php echo esc_html($c['modified']);?></li></ul></section><?php return ob_get_clean();
 }
}
ACLM_V75_AI_Document_Intelligence::init();
}
