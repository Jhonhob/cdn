<?php namespace B2\Modules\Templates\Widgets;

class History extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-history';

    //短代码名
	protected static $shortcode = 'b2_widget_history';

    //CMB2
	protected $cmb2 = null;

    //默认设置
	protected static $defaults = array();
    
    //实例
	protected $_instance = array();
    
    //cmb2项目
	protected $cmb2_fields = array();

    
	public function __construct() {
		parent::__construct(
			$this->widget_slug,
			__( 'B2-浏览历史', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '“浏览历史”小工具', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
			'history_title'=>__('浏览历史','b2'),
			'history_count'  => 10,
			'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
			array(
				'name'   => __('标题','b2'),
				'id_key' => 'history_title',
				'id'     => 'history_title',
				'type'   => 'text'
            ),
			array(
				'name'    => __( '要显示的数量', 'b2' ),
                'desc'    => __( '要显示的浏览历史条数，最多30条', 'b2' ),
                'id_key'      => 'history_count',
                'id'      => 'history_count',
                'type' => 'text',
                'attributes' => array(
                    'type' => 'number',
                    'pattern' => '\d*',
                ),
			)
        );
        
        //关于我们短代码
		add_shortcode( self::$shortcode, array( __CLASS__, 'b2_widget_history_us' ) );
	}
	
	/**
     * 刷新缓存
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function flush_widget_cache() {
		wp_cache_delete( $this->id, 'widget' );
	}

    /**
     * 显示小工具
     *
     * @param [type] $args
     * @param [type] $instance
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function widget( $args, $instance ) {
	
		echo self::get_widget( array(
			'args'     => $args,
			'instance' => $instance,
			'cache_id' => $this->id,
		) );
	}
    
    /**
     * 显示小工具短代码内容
     *
     * @param [type] $atts
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public static function get_widget( $atts ) {

        $atts['args']['cache_id'] = $atts['cache_id'];

		//获取设置项
		$instance = shortcode_atts(
			self::$defaults,
			! empty( $atts['instance'] ) ? (array) $atts['instance'] : array(),
			self::$shortcode
		);

		$atts = shortcode_atts(
			array(
				'instance'      => array(),
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '',
				'after_title'   => '',
				'cache_id'      => '',
				'flush_cache'   => isset( $_GET['delete-trans'] ), 
			),
			isset( $atts['args'] ) ? (array) $atts['args'] : array(),
			self::$shortcode
        );

		if ( empty( $atts['cache_id'] ) ) {
			$atts['cache_id'] = md5( serialize( $atts ) );
        }

		if(B2_OPEN_CACHE){
			$widget = ! $atts['flush_cache']
			? wp_cache_get( $atts['cache_id'], 'widget' )
            : '';
		}else{
			$widget = '';
		}

		// if(!empty($widget)) return $widget;

		$html = '
			<div class="history-widget" data-count="'.$instance['history_count'].'">
				<ul v-if="data.length > 0">
					<li v-for="item,i in data" class="b2flex">
						<a :href="item.url" target="_blank" class="link-block"></a>
						<img :src="item.thumb" class="thumb"/>
						<div class="history-content">
							<div class="history-meta"><span v-text="item.type" class="green"></span><time :datetime="item.time" itemprop="datePublished" class="b2timeago"></time></div>
							<div class="history-title"><a :href="item.url" v-text="item.title" target="_blank"></a></div>
						</div>
					</li>
				</ul>
				<div v-else v-cloak>
					<div class="empty-content-empty">'.__('暂无浏览历史','b2').'</div>
				</div>
			</div>
		';

		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			$widget .= '<div class="b2-widget-title b2flex">';
			$widget .= $atts['before_title']. esc_attr( $instance['history_title'] ) .$atts['after_title'];
			$widget .= '<span class="red" @click="clear">'.__('清空','b2').'</span></div>';
			$widget .= '<div class="b2-widget-box">'.$html.'</div>';
			$widget .= $atts['after_widget'];
			
			
			// if(B2_OPEN_CACHE){
			// 	wp_cache_set( $atts['cache_id'], $widget, 'widget', MINUTE_IN_SECONDS*30 );
			// }
			
		}

		return $widget;
	}
    
    /**
     * 更新小工具
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function update( $new_instance, $old_instance ) {
		$this->flush_widget_cache();
		$sanitized = $this->cmb2( true )->get_sanitized_values( $new_instance );
		return $sanitized;
	}
    
    /**
     * 小工具表单
     *
     * @param array $instance
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function form( $instance ) {
		// 如果没有设置项使用默认
		$this->_instance = wp_parse_args( (array) $instance, self::$defaults );
		$cmb2 = $this->cmb2();
		$cmb2->object_id( $this->option_name );
		\CMB2_hookup::enqueue_cmb_css();
		\CMB2_hookup::enqueue_cmb_js();
		$cmb2->show_form();
	}
    
    /**
     * 创建实例
     *
     * @param bool $saving
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function cmb2( $saving = false ) {

		$cmb2 = new \CMB2( array(
			'id'      => $this->option_name .'_box', 
			'hookup'  => false,
			'show_on' => array(
				'key'   => 'options-page',
				'value' => array( $this->option_name )
			),
		), $this->option_name );
		foreach ( $this->cmb2_fields as $field ) {
			if ( ! $saving ) {
				$field['id'] = $this->get_field_name( $field['id'] );
			}
			$field['default_cb'] = array( $this, 'default_cb' );
			$cmb2->add_field( $field );
		}
		return $cmb2;
	}
	/**
	 * 设置默认项
	 *
	 * @param  array      $field_args CMB2的设置项
	 * @param  CMB2_Field $field CMB2 选项对象
	 *
	 * @return mixed      Field value.
	 */
	public function default_cb( $field_args, $field ) {
		return isset( $this->_instance[ $field->args( 'id_key' ) ] )
			? $this->_instance[ $field->args( 'id_key' ) ]
			: null;
	}
}