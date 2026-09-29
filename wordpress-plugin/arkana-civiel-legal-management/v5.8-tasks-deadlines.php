<?php
/**
 * V5.8 Tasks & Deadlines foundation.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V58_Tasks_Deadlines' ) ) {
final class ACLM_V58_Tasks_Deadlines {
    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
        add_action( 'save_post_ac_task', array( __CLASS__, 'save_task' ), 10, 2 );
        add_action( 'save_post_ac_deadline', array( __CLASS__, 'save_deadline' ), 10, 2 );
        add_filter( 'manage_ac_task_posts_columns', array( __CLASS__, 'task_columns' ) );
        add_action( 'manage_ac_task_posts_custom_column', array( __CLASS__, 'task_column_value' ), 10, 2 );
        add_filter( 'manage_ac_deadline_posts_columns', array( __CLASS__, 'deadline_columns' ) );
        add_action( 'manage_ac_deadline_posts_custom_column', array( __CLASS__, 'deadline_column_value' ), 10, 2 );
        add_shortcode( 'arkana_task_dashboard', array( __CLASS__, 'dashboard' ) );
    }

    public static function meta_boxes() {
        add_meta_box( 'aclm_v58_task', 'Arkana Civiel — Task V5.8', array( __CLASS__, 'task_box' ), 'ac_task', 'normal', 'high' );
        add_meta_box( 'aclm_v58_deadline', 'Arkana Civiel — Deadline V5.8', array( __CLASS__, 'deadline_box' ), 'ac_deadline', 'normal', 'high' );
    }

    private static function select( $name, $value, $options ) {
        echo '<select name="' . esc_attr( $name ) . '" style="min-width:260px;">';
        foreach ( $options as $key => $label ) echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
        echo '</select>';
    }

    private static function users() {
        return get_users( array( 'role__in' => array( 'ac_managing_partner', 'ac_partner', 'ac_lawyer', 'ac_paralegal' ), 'orderby' => 'display_name', 'order' => 'ASC' ) );
    }

    public static function task_box( $post ) {
        wp_nonce_field( 'aclm_v58_task', 'aclm_v58_task_nonce' );
        $matter = (int) get_post_meta( $post->ID, '_aclm_matter_id', true );
        $request = (int) get_post_meta( $post->ID, '_aclm_request_id', true );
        $assignee = (int) get_post_meta( $post->ID, '_aclm_assignee', true );
        $status = get_post_meta( $post->ID, '_aclm_task_status', true ) ?: 'todo';
        $priority = get_post_meta( $post->ID, '_aclm_task_priority', true ) ?: 'normal';
        $due = get_post_meta( $post->ID, '_aclm_due_date', true );
        $matters = get_posts( array( 'post_type'=>'ac_matter', 'post_status'=>'publish', 'numberposts'=>100, 'orderby'=>'title', 'order'=>'ASC' ) );
        $requests = get_posts( array( 'post_type'=>'ac_request', 'post_status'=>'publish', 'numberposts'=>100, 'orderby'=>'date', 'order'=>'DESC' ) );
        ?>
        <p><strong>Matter</strong><br><select name="_aclm_matter_id" style="min-width:320px;"><option value="0">— Pilih Matter —</option><?php foreach($matters as $m): ?><option value="<?php echo esc_attr($m->ID); ?>" <?php selected($matter,$m->ID); ?>><?php echo esc_html($m->post_title); ?></option><?php endforeach; ?></select></p>
        <p><strong>Legal Request (opsional)</strong><br><select name="_aclm_request_id" style="min-width:320px;"><option value="0">— Tidak ditautkan —</option><?php foreach($requests as $r): ?><option value="<?php echo esc_attr($r->ID); ?>" <?php selected($request,$r->ID); ?>><?php echo esc_html($r->post_title); ?></option><?php endforeach; ?></select></p>
        <p><strong>Assignee</strong><br><?php self::user_select('_aclm_assignee',$assignee,self::users()); ?></p>
        <p><strong>Status</strong><br><?php self::select('_aclm_task_status',$status,array('todo'=>'To Do','in_progress'=>'In Progress','blocked'=>'Blocked','done'=>'Done','cancelled'=>'Cancelled')); ?></p>
        <p><strong>Priority</strong><br><?php self::select('_aclm_task_priority',$priority,array('low'=>'Low','normal'=>'Normal','high'=>'High','critical'=>'Critical')); ?></p>
        <p><strong>Due Date</strong><br><input type="date" name="_aclm_due_date" value="<?php echo esc_attr($due); ?>" /></p>
        <?php
    }

    public static function deadline_box( $post ) {
        wp_nonce_field( 'aclm_v58_deadline', 'aclm_v58_deadline_nonce' );
        $matter = (int) get_post_meta( $post->ID, '_aclm_matter_id', true );
        $owner = (int) get_post_meta( $post->ID, '_aclm_owner', true );
        $date = get_post_meta( $post->ID, '_aclm_due_date', true );
        $status = get_post_meta( $post->ID, '_aclm_deadline_status', true ) ?: 'open';
        $matters = get_posts( array( 'post_type'=>'ac_matter', 'post_status'=>'publish', 'numberposts'=>100, 'orderby'=>'title', 'order'=>'ASC' ) );
        ?>
        <p><strong>Matter</strong><br><select name="_aclm_matter_id" style="min-width:320px;"><option value="0">— Pilih Matter —</option><?php foreach($matters as $m): ?><option value="<?php echo esc_attr($m->ID); ?>" <?php selected($matter,$m->ID); ?>><?php echo esc_html($m->post_title); ?></option><?php endforeach; ?></select></p>
        <p><strong>Owner</strong><br><?php self::user_select('_aclm_owner',$owner,self::users()); ?></p>
        <p><strong>Deadline Date</strong><br><input type="date" name="_aclm_due_date" value="<?php echo esc_attr($date); ?>" /></p>
        <p><strong>Status</strong><br><?php self::select('_aclm_deadline_status',$status,array('open'=>'Open','completed'=>'Completed','extended'=>'Extended','missed'=>'Missed')); ?></p>
        <?php
    }

    private static function user_select($name,$value,$users) {
        echo '<select name="'.esc_attr($name).'" style="min-width:320px;"><option value="0">— Tidak ditetapkan —</option>';
        foreach($users as $u) echo '<option value="'.esc_attr($u->ID).'" '.selected($value,$u->ID,false).'>'.esc_html($u->display_name).'</option>';
        echo '</select>';
    }

    private static function verify($post_id,$nonce_key,$action,$cap='edit_post') {
        if(empty($_POST[$nonce_key]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_key])),$action)) return false;
        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return false;
        return current_user_can($cap,$post_id);
    }

    public static function save_task($post_id,$post) {
        if(!self::verify($post_id,'aclm_v58_task_nonce','aclm_v58_task')) return;
        foreach(array('_aclm_matter_id','_aclm_request_id','_aclm_assignee') as $key) if(isset($_POST[$key])) update_post_meta($post_id,$key,absint($_POST[$key]));
        foreach(array('_aclm_task_status'=>array('todo','in_progress','blocked','done','cancelled'),'_aclm_task_priority'=>array('low','normal','high','critical')) as $key=>$allowed) if(isset($_POST[$key])) { $v=sanitize_key(wp_unslash($_POST[$key])); if(in_array($v,$allowed,true)) update_post_meta($post_id,$key,$v); }
        if(isset($_POST['_aclm_due_date'])) { $v=sanitize_text_field(wp_unslash($_POST['_aclm_due_date'])); if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$v)) update_post_meta($post_id,'_aclm_due_date',$v); }
    }

    public static function save_deadline($post_id,$post) {
        if(!self::verify($post_id,'aclm_v58_deadline_nonce','aclm_v58_deadline')) return;
        foreach(array('_aclm_matter_id','_aclm_owner') as $key) if(isset($_POST[$key])) update_post_meta($post_id,$key,absint($_POST[$key]));
        if(isset($_POST['_aclm_due_date'])) { $v=sanitize_text_field(wp_unslash($_POST['_aclm_due_date'])); if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$v)) update_post_meta($post_id,'_aclm_due_date',$v); }
        if(isset($_POST['_aclm_deadline_status'])) { $v=sanitize_key(wp_unslash($_POST['_aclm_deadline_status'])); if(in_array($v,array('open','completed','extended','missed'),true)) update_post_meta($post_id,'_aclm_deadline_status',$v); }
    }

    public static function task_columns($c){$c['aclm_matter']='Matter';$c['aclm_assignee']='Assignee';$c['aclm_task_status']='Status';$c['aclm_due']='Due Date';return $c;}
    public static function task_column_value($col,$id){if('aclm_matter'===$col){$m=(int)get_post_meta($id,'_aclm_matter_id',true);echo $m?esc_html(get_the_title($m)):'—';}elseif('aclm_assignee'===$col){$u=get_user_by('id',(int)get_post_meta($id,'_aclm_assignee',true));echo $u?esc_html($u->display_name):'—';}elseif('aclm_task_status'===$col)echo esc_html(get_post_meta($id,'_aclm_task_status',true)?:'—');elseif('aclm_due'===$col)echo esc_html(get_post_meta($id,'_aclm_due_date',true)?:'—');}
    public static function deadline_columns($c){$c['aclm_matter']='Matter';$c['aclm_owner']='Owner';$c['aclm_deadline_status']='Status';$c['aclm_due']='Deadline';return $c;}
    public static function deadline_column_value($col,$id){if('aclm_matter'===$col){$m=(int)get_post_meta($id,'_aclm_matter_id',true);echo $m?esc_html(get_the_title($m)):'—';}elseif('aclm_owner'===$col){$u=get_user_by('id',(int)get_post_meta($id,'_aclm_owner',true));echo $u?esc_html($u->display_name):'—';}elseif('aclm_deadline_status'===$col)echo esc_html(get_post_meta($id,'_aclm_deadline_status',true)?:'—');elseif('aclm_due'===$col)echo esc_html(get_post_meta($id,'_aclm_due_date',true)?:'—');}

    public static function dashboard() {
        if(!is_user_logged_in()) return '<p>Silakan login untuk melihat task dashboard.</p>';
        $uid=get_current_user_id();
        $tasks=new WP_Query(array('post_type'=>'ac_task','post_status'=>'publish','posts_per_page'=>20,'meta_query'=>array(array('key'=>'_aclm_assignee','value'=>(string)$uid,'compare'=>'='))));
        ob_start(); ?>
        <section class="aclm-task-dashboard" style="max-width:1100px;margin:40px auto;padding:0 20px;"><h1>My Tasks & Deadlines</h1><p>Task yang ditugaskan kepada akun Anda.</p><ul><?php if(!$tasks->have_posts()): ?><li>Belum ada task.</li><?php else: while($tasks->have_posts()):$tasks->the_post();$id=get_the_ID(); ?><li><strong><?php echo esc_html(get_the_title()); ?></strong> — <?php echo esc_html(get_post_meta($id,'_aclm_task_status',true)?:'—'); ?> — Due: <?php echo esc_html(get_post_meta($id,'_aclm_due_date',true)?:'—'); ?></li><?php endwhile; wp_reset_postdata(); endif; ?></ul></section>
        <?php return ob_get_clean();
    }
}
ACLM_V58_Tasks_Deadlines::init();
}
