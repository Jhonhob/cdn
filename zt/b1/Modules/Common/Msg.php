<?php namespace B2\Modules\Common;
class Msg{

    public function init(){
        add_filter( 'b2_order_callback_gx', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_ds', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_cz', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_w', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_x', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_cg', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_v', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_verify', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_vip', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_custom', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_infomation_sticky', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_circle_join', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_circle_read_answer_pay', array($this,'send_email'),9998,2);
        add_filter( 'b2_order_callback_circle_hidden_content_pay', array($this,'send_email'),9998,2);
    }

    public function send_email($money,$data){

        $admin_email = b2_get_option('normal_mail_message','msg_admin_email');

        if($data['order_type'] == 'ds'){
            $open = b2_get_option('normal_mail_message','msg_ds');
            $open =  $open === '' ? true : $open;
            if($open){
                $author = get_post_field( 'post_author', $data['post_id'] );
                $user_data = get_userdata($data['user_id']);
                self::send_email_to_user(
                    [
                        'user_id' => $author,
                        'object' => __('有人给您打赏了','b2'),
                        'content' => '<p>'.sprintf(__('%s在%s中给您打赏了%s','b2'),
                        '「'.$user_data->display_name.'」',
                        '<a href="'.get_permalink($data['post_id']).'" target="_blank">'.get_the_title($data['post_id']).'</a>',
                        B2_MONEY_SYMBOL.$data['order_total']
                        ).'</p>'
                    ]
                );
            }
        }elseif($data['order_type'] == 'cz'){
            $open = b2_get_option('normal_mail_message','msg_cz');
            $open =  $open === '' ? true : $open;
            $user_data = get_userdata($data['user_id']);
            if($open){
                self::send_email_to_user(
                    [
                        'email' => $admin_email,
                        'object' => __('用户充值提醒','b2'),
                        'content' => '<p>'.sprintf(__('%s充值了%s','b2'),
                        '「'.$user_data->display_name.'」',
                        B2_MONEY_SYMBOL.$data['order_total']
                        ).'</p>'
                    ]
                );
            }
        }elseif($data['order_type'] == 'w'){
            $open = b2_get_option('normal_mail_message','msg_w');
            $open =  $open === '' ? true : $open;
            if($open){
                $author = get_post_field( 'post_author', $data['post_id'] );
                $user_data = get_userdata($data['user_id']);
                self::send_email_to_user(
                    [
                        'user_id' => $author,
                        'object' => __('隐藏内容出售成功','b2'),
                        'content' => '<p>'.sprintf(__('%s在%s中支付了%s购买您的隐藏内容','b2'),
                        '「'.$user_data->display_name.'」',
                        '<a href="'.get_permalink($data['post_id']).'" target="_blank">'.get_the_title($data['post_id']).'</a>',
                        B2_MONEY_SYMBOL.$data['order_total']
                        ).'</p>'
                    ]
                );
            }
        }elseif($data['order_type'] == 'x'){
            $open = b2_get_option('normal_mail_message','msg_x');
            $open =  $open === '' ? true : $open;

            if($open){
                $author = get_post_field( 'post_author', $data['post_id'] );
                $user_data = get_userdata($data['user_id']);
                self::send_email_to_user(
                    [
                        'user_id' => $author,
                        'object' => __('资源出售成功','b2'),
                        'content' => '<p>'.sprintf(__('%s在%s中支付了%s购买您的下载内容','b2'),
                        '「'.$user_data->display_name.'」',
                        '<a href="'.get_permalink($data['post_id']).'" target="_blank">'.get_the_title($data['post_id']).'</a>',
                        B2_MONEY_SYMBOL.$data['order_total']
                        ).'</p>'
                    ]
                );
            }
        }elseif($data['order_type'] == 'cg'){
            $open = b2_get_option('normal_mail_message','msg_cg');
            $open =  $open === '' ? true : $open;
            if($open){
                $user_data = get_userdata($data['user_id']);
                self::send_email_to_user(
                    [
                        'email' => $admin_email,
                        'object' => __('有人购买了积分','b2'),
                        'content' => '<p>'.sprintf(__('%s在购买了积分','b2'),
                        '「'.$user_data->display_name.'」'
                        ).'</p>'
                    ]
                );
            }
        }elseif($data['order_type'] == 'v'){
            $open = b2_get_option('normal_mail_message','msg_v');
            $open =  $open === '' ? true : $open;
            if($open){
                $author = get_post_field( 'post_author', $data['post_id'] );
                $user_data = get_userdata($data['user_id']);
                self::send_email_to_user(
                    [
                        'user_id' => $author,
                        'object' => __('视频出售成功','b2'),
                        'content' => '<p>'.sprintf(__('%s在%s中支付了%s购买您的视频','b2'),
                        '「'.$user_data->display_name.'」',
                        '<a href="'.get_permalink($data['post_id']).'" target="_blank">'.get_the_title($data['post_id']).'</a>',
                        B2_MONEY_SYMBOL.$data['order_total']
                        ).'</p>'
                    ]
                );
            }
        }elseif($data['order_type'] == 'verify'){
            $open = b2_get_option('normal_mail_message','msg_verify');
            $open =  $open === '' ? true : $open;
            if($open){
                $user_data = get_userdata($data['user_id']);
                self::send_email_to_user(
                    [
                        'email' => $admin_email,
                        'object' => __('认证申请','b2'),
                        'content' => '<p>'.sprintf(__('%s在申请了认证，请审核','b2'),
                        '「'.$user_data->display_name.'」'
                        ).'</p>'
                    ]
                );
            }
        }elseif($data['order_type'] == 'circle_join'){
            $open = b2_get_option('normal_mail_message','msg_circle_join');
            $open =  $open === '' ? true : $open;
            $circle_name = b2_get_option('normal_custom','custom_circle_name');
            if($open){
                $author = get_term_meta($data['post_id'],'b2_circle_admin',true);
                $user_data = get_userdata($data['user_id']);
                self::send_email_to_user(
                    [
                        'user_id' => $author,
                        'object' => sprintf(__('有人加入了您的%s','b2'),$circle_name),
                        'content' => '<p>'.sprintf(__('%s支付了%s加入了您的%s','b2'),
                        '「'.$user_data->display_name.'」',
                        B2_MONEY_SYMBOL.$data['order_total'],
                        $circle_name
                        ).'</p>'
                    ]
                );
            }
        }elseif($data['order_type'] == 'circle_read_answer_pay'){
            $open = b2_get_option('normal_mail_message','msg_circle_read_answer_pay');
            $open =  $open === '' ? true : $open;
            if($open){
                $author = get_post_field( 'post_author', $data['post_id'] );
                $user_data = get_userdata($data['user_id']);
                self::send_email_to_user(
                    [
                        'user_id' => $author,
                        'object' => __('有人付费查看回答','b2'),
                        'content' => '<p>'.sprintf(__('%s支付了%s付费查看回答','b2'),
                        '「'.$user_data->display_name.'」',
                        B2_MONEY_SYMBOL.$data['order_total']
                        ).'</p>'
                    ]
                );
            }
        }elseif($data['order_type'] == 'circle_hidden_content_pay'){
            $open = b2_get_option('normal_mail_message','msg_circle_hidden_content_pay');
            $open =  $open === '' ? true : $open;
            if($open){
                $author = get_post_field( 'post_author', $data['post_id'] );
                $user_data = get_userdata($data['user_id']);
                self::send_email_to_user(
                    [
                        'user_id' => $author,
                        'object' => __('有人付费查看隐藏帖子','b2'),
                        'content' => '<p>'.sprintf(__('%s支付了%s付费查看隐藏帖子','b2'),
                        '「'.$user_data->display_name.'」',
                        B2_MONEY_SYMBOL.$data['order_total']
                        ).'</p>'
                    ]
                );
            }
        }elseif($data['order_type'] == 'vip'){
            $open = b2_get_option('normal_mail_message','msg_vip');
            $open =  $open === '' ? true : $open;
            if($open){
                $user_data = get_userdata($data['user_id']);
                self::send_email_to_user(
                    [
                        'email' => $admin_email,
                        'object' => __('有人购买了会员','b2'),
                        'content' => '<p>'.sprintf(__('%s支付%s购买了会员','b2'),
                        '「'.$user_data->display_name.'」',
                        B2_MONEY_SYMBOL.$data['order_total']
                        ).'</p>'
                    ]
                );
            }
            
        }elseif($data['order_type'] == 'custom'){
            $open = b2_get_option('normal_mail_message','msg_custom');
            $open =  $open === '' ? true : $open;
            if($open){
                $user_data = get_userdata($data['user_id']);
                self::send_email_to_user(
                    [
                        'email' => $admin_email,
                        'object' => __('有人提交了表单','b2'),
                        'content' => '<p>'.sprintf(__('%s支付%s提交了表单','b2'),
                        '「'.$user_data->display_name.'」',
                        B2_MONEY_SYMBOL.$data['order_total']
                        ).'</p>'
                    ]
                );
            }
            
        }elseif($data['order_type'] == 'infomation_sticky'){
            $open = b2_get_option('normal_mail_message','msg_infomation_sticky');
            $open =  $open === '' ? true : $open;
            if($open){
                $user_data = get_userdata($data['user_id']);
                $info_name = b2_get_option('normal_custom','custom_infomation_name');
                self::send_email_to_user(
                    [
                        'email' => $admin_email,
                        'object' => sprintf(__('有人置顶了%s','b2'),$info_name),
                        'content' => '<p>'.sprintf(__('%s支付%s置顶了%s帖子','b2'),
                        '「'.$user_data->display_name.'」',
                        B2_MONEY_SYMBOL.$data['order_total'],
                        $info_name
                        ).'</p>'
                    ]
                );
            }
            
        }
    }

    public static function send_email_to_user($arg){
        $site_name = B2_BLOG_NAME;

        $arg = apply_filters('b2_send_email_to_user',$arg);

        if(!isset($arg['email'])){
            if(!isset($arg['user_id'])) return;

            $user_data =  get_user_by('id', (int)$arg['user_id']);
    
            if(!$user_data) return;
    
            if(!$user_data->user_email) return;

            $email = $user_data->user_email;
        }else{
            $email = $arg['email'];
        }

        $subject = isset($arg['object']) ? $arg['object'] : sprintf(__('来自%s的通知','b2'),$site_name);

        if(!isset($arg['content']) || !$arg['content']) return;

        $message = '<div style="width:700px;background-color:#fff;margin:0 auto;border: 1px solid #ccc;">
            <div style="height:64px;margin:0;padding:0;width:100%;">
                <a href="'.B2_HOME_URI.'" style="display:block;padding: 12px 30px;text-decoration: none;font-size: 24px;letter-spacing: 3px;border-bottom: 1px solid #ccc;" rel="noopener" target="_blank">
                    '.$site_name.'
                </a>
            </div>
            <div style="padding: 30px;margin:0;">
                <p style="font-size:14px;color:#333;">
                    '.$subject.'
                </p>
                '.$arg['content'].'
                <p style="font-size:14px;color: #999;">— '.$site_name.'</p>
                <p style="font-size:12px;color:#999;border-top:1px dotted #E3E3E3;margin-top:30px;padding-top:30px;">
                    '.__('本邮件为系统邮件，请勿回复。','b2').'
                </p>
            </div>
        </div>';

        $send = wp_mail( $email, $subject, $message );

        if(!$send){
            return false;
        }

        return true;
    }

}