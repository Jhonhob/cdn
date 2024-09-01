<?php
namespace B2\Modules\Templates\Modules;

use B2\Modules\Common\Coupon;
use B2\Modules\Common\Shop;

class Products{
    public function init($data,$i,$return = false){
        if(!$data) return;
        
        return self::normal($data,$i,$return);
    }

    public static function ids_to_html($ids,$w,$h,$ratio,$open){
        $list_html = '';

        foreach ($ids as $k => $v) {
            $v = trim($v, " \t\n\r\0\x0B\xC2\xA0");
            if($v){

                $list = Shop::get_shop_item_data($v,0);
                
                $thumb = b2_get_thumb(array('thumb'=>$list['thumb_full'],'width'=>round($w),'height'=> $h,'ratio'=>2));

                if($list['type'] === 'normal'){
                    $price = '<div class="shop-box-price">
                        <span>'.B2_MONEY_SYMBOL.$list['price']['current_price'].'</span>
                        '.($list['price']['price'] !== $list['price']['current_price'] ? '<span class="delete-line">'.B2_MONEY_SYMBOL.$list['price']['price'].'</span>' : '').'
                    </div>';
                    $key = '';
                    $button = __('购买','b2');
                }elseif($list['type'] === 'lottery'){
                    $price = '<div class="shop-box-price shop-box-credit">
                        <span>'.b2_get_icon('b2-coin-line').$list['price']['credit'].'</span>
                    </div>';
                    $key = __('抽奖','b2');
                    $button = __('抽奖','b2');
                }else{
                    $price = '<div class="shop-box-price shop-box-credit">
                        <span>'.b2_get_icon('b2-coin-line').$list['price']['credit'].'</span>
                    </div>'; 
                    $key = __('兑换','b2');
                    $button = __('兑换','b2');
                }

                $list_html .= '<li><div class="shop-list-item b2-radius box">
                    <div class="shop-list-thumb">
                        <div class="shop-list-thumb-in" style="padding-top:'.$ratio.'%">
                            <div class="shop-list-thumb-in-info">
                            '.b2_get_img(array('src'=>$thumb,'alt'=>$list['title'])).($key ? '<span class="shop-box-type">'.$key.'</span>' : '').'
                            '.($list['type'] === 'normal' && $list['price']['price_text'] ? '<span class="shop-normal-tips">'.$list['price']['price_text'].'</span>' : '').'
                            </div>
                        </div>
                    </div>
                    <h2>'.$list['title'].'</h2>
                    <a href="'.$list['link'].'" class="link-block" '.$open.'></a>
                    '.$price.'
                    <div class="shop-normal-item-count">
                        <span>'.__('库存：','b2').'<b>'.$list['stock']['total'].'</b></span>
                        <span>'.__('人气：','b2').'<b>'.$list['views'].'</b></span>
                    </div>
                    </div>
                </li>';
            }
        }

        return $list_html;
    }

    public function normal($data,$i,$return){

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $coupons = array();

        if(isset($data['products_coupons']) && $data['products_coupons']){
            $ids = $this->str_to_array($data['products_coupons']);
            $coupons = Coupon::get_coupons($ids);
        }
        
        $list_html = '';

        $radio = isset($data['products_thumb_ratio']) && $data['products_thumb_ratio'] ? $data['products_thumb_ratio'] : '1/1';

        $ratio = explode('/',$radio);
        $w_ratio = $ratio[0];
        $h_ratio = $ratio[1];

        $data['products_count'] = (int)$data['products_count'] ? (int)$data['products_count'] : 3;

        $w = ($data['width']/(int)$data['products_count']) - (B2_GAP*2);

        $h = round($w/$w_ratio*$h_ratio);

        $ratio = round($h_ratio/$w_ratio*100,6);

        $open = self::open_type($data);

        $p_ids = [];

        if(isset($data['products_cats']) && $data['products_cats']){
            $args = array(
                'post_type' => 'shop',
                'orderby'  => 'date',
                'order'=>'DESC',
                'post_status'=>'publish',
                'paged'=>1,
                'offset'=>0,
                'no_found_rows'=>true,
                'posts_per_page'=>isset($data['products_page_total']) && $data['products_page_total'] ? (int)$data['products_page_total'] : 8,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'shoptype',
                        'field' => 'term_id',
                        'terms' => $data['products_cats'],
                        'include_children' => true,
                    )
                )
            );

            $shop_the_query = new \WP_Query( $args );

            if ( $shop_the_query->have_posts()) {
                while ( $shop_the_query->have_posts() ) {
                    $shop_the_query->the_post();
                    $p_ids[] = get_the_ID();
                }
            }
            wp_reset_postdata();

        }else{
            $p_ids = $this->str_to_array($data['products_ids']);
        }

        if(!empty($p_ids)){
            $list_html .= self::ids_to_html($p_ids,$w,$h,$ratio,$open);
        }

        //优惠劵列表
        $list_coupon = '';
        if(!empty($coupons)){
            foreach ($coupons as $k => $v) {

                $products = $v['products'];

                $desc = '';
                if(!empty($products)){
                    $title = __('限制商品','b2');
                    $type = 'stamp01';
                    foreach ($products as $_k => $_v) {
                        $thumb = b2_get_thumb(array('thumb'=>$_v['image'],'height'=>80,'width'=>80));

                        $desc .= '<a href="'.$_v['link'].'" target="_blank">
                        '.b2_get_img(array('src'=>$thumb,'alt'=>$_v['name'])).'
                        </a> ';
                    }
                }elseif(!empty($v['cats'])){
                    $title = __('限制商品分类','b2');
                    $type = 'stamp02';
                    foreach ($v['cats'] as $c_k => $c_v) {
                        $desc .= '[<a href="'.$c_v['link'].'" target="_blank">'.$c_v['name'].'</a>] ';
                    }
                }else{
                    $title = __('不限制使用','b2');
                    $type = 'stamp03';
                    $desc .= __('所有商品和商品类型均可使用','b2');
                }

                $roles = '';
                if(!empty($v['roles']['lvs'])){
                    foreach ($v['roles']['lvs'] as $r_k => $r_v) {
                        $roles .= $r_v.' ';
                    }
                }else{
                    $roles = __('任何人都可以使用','b2'); 
                }

                $date = '';
                if($v['receive_date']['expired']){
                    $date = '<div class="coupon-desc">'.__('领取时间','b2').'</div>'.__('无法领取','b2');
                    $type = 'stamp04';
                }else{
                    if((int)$v['receive_date']['date'] === 0){
                        $date = '<div class="coupon-desc">'.__('领取时间','b2').'</div>'.__('随时领取','b2');
                    }else{
                        $date = '<div class="coupon-desc">'.__('领取时间截止到','b2').'</div>'.$v['receive_date']['date'];
                    }
                }

                $shixiao = '';
                if((int)$v['expiration_date']['date'] !== 0){
                    $shixiao = '<div class="coupon-desc">'.__('使用时效：','b2').'</div>'.$v['expiration_date']['date'].__('天内使用有效','b2');
                }else{
                    $shixiao = '<div class="coupon-desc">'.__('使用时效：','b2').'</div>'.__('永久有效','b2');
                }

                $list_coupon .= '
                <div class="shop-coupon-item">
                    <div class="stamp '.$type.' b2-radius">
                        <div class="par">
                            <p>'.$title.'</p>
                            <sub class="sign">'.B2_MONEY_SYMBOL.'</sub><span>'.$v['money'].'</span><sub>'.__('优惠劵','b2').'</sub>
                            <div class="coupon-date">
                                <div>'.$shixiao.'</div>
                            </div>
                        </div>
                        <div class="copy">
                        <div class="copy-date">'.$date.'</div>
                            <p><button '.($type === 'stamp04' ? 'disabled="true"' : false).' class="coupon-receive" data-id="'.$v['id'].'">'.($type === 'stamp04' ? __('已经过期','b2') : __('立刻领取','b2')).'</button></p>
                            <div class="coupon-info-box">
                                <button class="text more-coupon-info">'.b2_get_icon('b2-information-line').__('查看详情','b2').'</button>
                                <div class="coupon-info b2-radius">
                                    <div class="shop-coupon-title"><div class="coupon-title"><span>'.__('优惠劵ID：','b2').'</span><span class="coupon-id">'.$v['id'].'</span></div><span class="close-coupon-info">×</span></div>
                                    <div class="">
                                        <span class="coupon-title">'.$title.'：</span>
                                        <div class="">'.$desc.'</div>
                                    </div>
                                    <div class="coupon-roles">
                                        <span class="coupon-title">'.__('限制用户组','b2').'：</span>
                                        <div class="coupon-roles-desc">'.$roles.'</div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <i class="coupon-bg"></i>
                    </div>
                </div>
                ';
            }
        }

        $count = isset($data['products_count']) && $data['products_count'] ? $data['products_count'] : 5;
        $r = round(1/$count,6)*100;

        $settings = [
            'count'=>isset($data['products_page_total']) ? (int)$data['products_page_total'] : 8,
            'terms'=>isset($data['products_cats']) ? $data['products_cats'] : [],
            'ratio'=>$ratio,
            'w'=>$w,
            'h'=>$h,
            'open'=>$open
        ];

        $json_setting = "data-opts='".json_encode($settings,true)."'";
        return '
            <style>
                .shop-box-item-'.$i.' .shop-box-list li{
                    max-width:'.$r.'%
                }
            </style>
            <div class="shop-box '.(!$list_coupon ? 'shop-box-none-coupon' : '').' '.(!$list_html ? 'shop-box-none-product' : '').' shop-box-item-'.$i.'" '.$json_setting.'>
                '.$this->get_modules_title($data).'
                <div class="shop-box-row">
                    '.($list_coupon ? '<div class="shop-coupon-box">'.$list_coupon.'</div>' : '').'
                    '.($list_html ? '<div class="shop-box-list"><div class="hidden-line"><ul class="b2_gap">'.$list_html.'</ul></div></div>' : '').'
                </div>
            </div>
        ';
    }

    public function str_to_array($str){
        if(strpos($str,'new|') !== false){
            $count = (int)str_replace('new|','',$str);
            $products = wp_get_recent_posts(array(
                'numberposts' => $count, 
                'post_status' => 'publish',
                'post_type'=>'shop',
                'posts_per_archive_page'=>false
            ));

            return array_column($products,'ID');

        }else{
            $str = trim($str, " \t\n\r\0\x0B\xC2\xA0");
            $str = explode(PHP_EOL, $str );
            return $str;
        }
    }

    public static function open_type($data){
        //是否新窗口打开
        $open = isset($data['products_open_type']) ? $data['products_open_type'] : '';

        if(!$open){
            return ' target="_blank"';
        }else{
            return '';
        }
    }

    public function get_modules_title($data){

        $post_meta = isset($data['products_meta']) && is_array($data['products_meta']) ? $data['products_meta'] : array();

        $title = in_array('title',$post_meta);
        $html = '';
        $desc = in_array('desc',$post_meta);
        $html .= '<div class="shop-box-title"><div class="modules-title-box">';
        if($title && isset($data['title'])){
            
            $html .= '<h2 class="module-title">'.$data['title'].'</h2>';
            
        }

        $html .= '<div class="shop-module-top-right">';

        $links = in_array('links',$post_meta);
        if($links){
            $cats = isset($data['products_cats']) && !empty($data['products_cats']) ? $data['products_cats'] : [];

            if(!empty($cats)){
                $html .= '<div class="shop-box-links post-list-cats">';

                $html .= '<a :class="[{picked:id==0},\'post-load-button\']" href="javascript:void(0)" @click="getGoods(0)"><span>'.__('全部','b2').'</span></a>';

                foreach ($cats as $v) {

                    //通过ID获取商品分类名称和链接
                    $term = get_term_by('id',$v,'shoptype');
                    if(!$term) continue;
                    $html .= '<a href="javascript:void(0)" @click="getGoods('.$term->term_id.')" :class="[{picked:id=='.$term->term_id.'},\'post-load-button\']"><span>'.$term->name.'</span></a>';
                }
                $html .= '</div>';
            }
        }

        $more_url = isset($data['module_more_url']) && $data['module_more_url'] ? $data['module_more_url'] : '';
        if( !$more_url ) $more_url = get_post_type_archive_link('shop');

        $html .= '<div class="post-list-cats"><a href="'.$more_url.'" target="_blank"><span>'.__('更多','b2').b2_get_icon('b2-arrow-right-s-line').'</span></a></div></div></div></div>';


        if(!$desc && !$title && !$links){
            return '';
        }else{
            return $html;
        }
    }
}
