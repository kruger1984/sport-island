<?php

$widgets = [
    'widget-text.php',
    'widget-contacts.php',
    'widget-social-links.php',
    'widget-iframe.php',
    'widget-info.php'
];

foreach ($widgets as $w) {
    require_once ( __DIR__ . '/inc/' . $w);
}


add_action( 'after_setup_theme', 'si_setup' );
add_action( 'wp_enqueue_scripts', 'si_scripts' );
add_action( 'widgets_init', 'si_register' );
add_action( 'init', 'si_register_types');
add_action( 'add_meta_boxes', 'si_meta_boxes'); 
add_action( 'admin_init', 'si_register_slogan' );
//add_action( 'save_post', 'si_save_like_meta');
add_action('admin_post_nopriv_si-modal-form', 'si_modal_form_handler');
add_action('admin_post_si-modal-form', 'si_modal_form_handler');
add_action('wp_ajax_nopriv_post-likes', 'si_likes');
add_action('wp_ajax_post-likes', 'si_likes');
add_shortcode( 'si-paste-link', 'si_paste_link' );
add_action( 'manage_posts_custom_column', 'si_like_column', 5, 2 );
/* отключить редактор gutenberg в виджетах */
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
add_filter( 'use_widgets_block_editor', '__return_false' );
add_filter( 'manage_posts_columns', 'si_add_col_likes' );


add_filter( 'show_admin_bar', '__return_false' );
add_filter( 'si_widget_text', 'do_shortcode' );



//Оптимизация шапки wordpress
remove_action('wp_head','feed_links_extra', 3); // убирает ссылки на rss категорий
remove_action('wp_head','feed_links', 2); // минус ссылки на основной rss и комментарии
remove_action('wp_head','rsd_link');  // сервис Really Simple Discovery
remove_action('wp_head','wlwmanifest_link'); // Windows Live Writer
remove_action('wp_head','wp_generator');  // скрыть версию wordpress
remove_action('wp_head','start_post_rel_link',10,0);
remove_action('wp_head','index_rel_link');
remove_action('wp_head','adjacent_posts_rel_link_wp_head', 10, 0 );
remove_action('wp_head','wp_shortlink_wp_head', 10, 0 );
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
 //

function si_setup() {
    register_nav_menu( 'menu-header', 'Меню в шапке' );
    register_nav_menu( 'menu-footer', 'Меню в подвале' );

    add_theme_support( 'custom-logo' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    //add_theme_support( 'menus' );
}

function si_scripts(){
    wp_enqueue_script(
        'js',
        _si_assets_path( '/js/js.js' ),
        [],
        '1.1',
        true,
    );
    wp_enqueue_style(
        'si-style',
        _si_assets_path( '/css/styles.css' ),
        [],
        '1.0',
        'all'
    );

		//Оптимизация Head
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'bodhi-svgs-attachment' );
		wp_dequeue_style( 'wp-embed' );
		wp_deregister_script( 'wp-embed' );

		// if ( !is_admin() ) {
		// 	wp_deregister_script('jquery'); 
		// }
}
function si_register_slogan(){
	add_settings_field(
		'si_option_field_slogan',
		'Слоган вашего сайта: ',
		'si_slogan_option_cb',
		'general',
		'default',
		['label_for'=>'si_option_field_slogan']
	);
	register_setting(
		'general', 
		'si_option_field_slogan',
		'strval',
	);
}

function si_slogan_option_cb( $args ){
	$slug = $args['label_for'];
?>
	<input 
		type="text"
		id="<?php echo $slug; ?>"
		value="<?php echo get_option( $slug ); ?>"
		name="<?php echo $slug; ?>"
		class="regular-text"
	>
<?php
}

function _si_assets_path (string $path) : string {
    return get_template_directory_uri() . '/assets/' . $path;
}

function si_register(){
    register_sidebar([
        'name' => 'Контакты в шапке',
        'id' => 'si-header',
        'before_widget' => null,
        'after_widget' => null
    ]);
    register_sidebar([
        'name' => 'Контакты в подвале',
        'id' => 'si-footer',
        'before_widget' => null,
        'after_widget' => null
    ]);
    register_sidebar([
        'name' => 'Сайдбар в подвале зона 1',
        'id' => 'si-footer-column-1',
        'before_widget' => null,
        'after_widget' => null
    ]);
    register_sidebar([
        'name' => 'Сайдбар в подвале зона 2',
        'id' => 'si-footer-column-2',
        'before_widget' => null,
        'after_widget' => null
    ]);
    register_sidebar([
        'name' => 'Сайдбар в подвале зона 3',
        'id' => 'si-footer-column-3',
        'before_widget' => null,
        'after_widget' => null
    ]);
    register_sidebar([
        'name' => 'Карта',
        'id' => 'si-map',
        'before_widget' => null,
        'after_widget' => null
    ]);
    register_sidebar([
        'name' => 'Сайдбар под картой',
        'id' => 'si-after-map',
        'before_widget' => null,
        'after_widget' => null
    ]);
    register_widget( 'si_widget_text' );
    register_widget( 'si_widget_contacts' );
    register_widget( 'si_widget_social_links' );
    register_widget( 'si_widget_iframes' );
    register_widget( 'si_widget_info' );

}

function si_paste_link( $attr ){
    $params = shortcode_atts( [
        'link' => '',
        'text' => '',
        'type' => 'link',
    ], $attr );
    $params['text'] = $params['text'] ? $params['text'] : $params['link'];
    if( $params['link'] ){
        $protocol = '';
        switch($params['type']){
            case 'email':
                $protocol = 'mailto:';
            break;
            case 'phone':
                $protocol = 'tel:';
                $params['link'] = preg_replace( '#[^+0-9/]#', '', $params['link']);
            break;     
            default:
                $protocol = '';
            break;   
        }
        $link = $protocol . $params['link'];
        $text = $params['text'];
        return "<a href=\"{$link}\">{$text}</a> ";
    } else {
        return '';
    }
};

function si_meta_boxes(){
	add_meta_box(
		'si-like',
		'Количество лайков: ',
		'si_meta_like_cb',
		'post'
	);
	$fields = [
		'si_order_date' => 'Дата заявки: ',
		'si_order_name' => 'Имя клиента: ',
		'si_order_phone' => 'Номер телефона: ',
		'si_order_choice' => 'Выбор клиента: ',
	];
	foreach ( $fields as $slug => $text ){
		add_meta_box(
			$slug,
			$text,
			'si_order_fields_cb',
			'orders',
			'advanced',
			'default',
			$slug

		);
	}

};

function si_meta_like_cb( $post_obj ){
	$likes = get_post_meta( $post_obj->ID, 'si-like', true );
	$likes = $likes ? $likes : 0;
	//echo "<input type=\"text\" name=\"si-like\" value=\"{$likes}\">";
	echo '<p>' . $likes . '<p>';
}

function si_order_fields_cb( $post_obj, $slug ){
	$slug = $slug['args'];
	$data = '';
	switch( $slug ){
		case 'si_order_date':
			$data = $post_obj->post_date;
			
		break;
		case 'si_order_choice':
			$id = get_post_meta( $post_obj->ID, $slug, true );
			$title = get_the_title( $id );
			$type = get_post_type_object( get_post_type( $id ) )->labels->name;
			$data = 'Клиент выбрал: <strong>' . $title . '</strong>. <br> Из раздела: <strong>' . $type.'</strong>'; 
		break;
		default:
				$data = get_post_meta( $post_obj->ID, $slug , true );
				$data = $data ? $data : 'Нет данных';
		break;
	}


	echo '<p>' . $data . '</p>';
}

// function si_save_like_meta( $post_id ){
// 	if ( isset($_POST['si-like'] )){
// 		update_post_meta( $post_id, 'si-like', $_POST['si-like']  );
// 	}
// }

function si_register_types(){
  register_post_type( 'services', [
			'taxonomies' => [], // post related taxonomies
			'label'  => null,
			'labels' => [
					'name'               => 'Услуги', // name for the post type.
					'singular_name'      => 'Услуга', // name for single post of that type.
					'add_new'            => 'Добавить новую услугу', // to add a new post.
					'add_new_item'       => 'Добавить новую услугу', // title for a newly created post in the admin panel.
					'edit_item'          => 'Редактировать услугу', // for editing post type.
					'new_item'           => 'Новая услуга', // new post's text.
					'view_item'          => 'Смотреть услуги', // for viewing this post type.
					'search_items'       => 'Искать услуги', // search for these post types.
					'not_found'          => 'Не найдено в корзине', // if search has not found anything.
					'not_found_in_trash'          => 'Не найдено', // if search has not found anything.
					'parent_item_colon'  => '', // for parents (for hierarchical post types).
					'menu_name'          => 'Услуги', // menu name.
			],
		'public'              => true,
		//'publicly_queryable'  => null, // depends on public
		//'exclude_from_search' => null, // depends on public
		//'show_ui'             => null, // depends on public
		//'show_in_nav_menus'   => null, // depends on public
		//'show_in_menu'        => null, // whether to in admin panel menu
		//'show_in_admin_bar'   => null, // depends on show_in_menu.
		//'show_in_rest'        => null, // Add to REST API. WP 4.7.
		//'rest_base'           => null, // $post_type. WP 4.7.
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-smiley',
		//'capability_type'   => 'post',
		//'capabilities'      => 'post', // Array of additional rights for this post type.
		//'map_meta_cap'      => null, // Set to true to enable the default handler for meta caps.
		'hierarchical'        => false,
		// [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats' ]
		'supports'            => [ 'title' ],
		'has_archive'         => true, //false - wp не зарегистрирует урл под тип записей.
		//'rewrite'             => true,
		//'query_var'           => true,
    ] );
  register_post_type( 'trainers', [
			'taxonomies' => [], // post related taxonomies
			'label'  => null,
			'labels' => [
			'name'               => 'Тренеры', // name for the post type.
			'singular_name'      => 'Тренер', // name for single post of that type.
			'add_new'            => 'Добавить нового тренера', // to add a new post.
			'add_new_item'       => 'Добавить нового тренера', // title for a newly created post in the admin panel.
			'edit_item'          => 'Редактировать тренера', // for editing post type.
			'new_item'           => 'Новый тренер', // new post's text.
			'view_item'          => 'Смотреть тренера', // for viewing this post type.
			'search_items'       => 'Искать тренера', // search for these post types.
			'not_found'          => 'Не найдено в корзине', // if search has not found anything.
			'not_found_in_trash'          => 'Не найдено', // if search has not found anything.
			'parent_item_colon'  => '', // for parents (for hierarchical post types).
			'menu_name'          => 'Тренеры', // menu name.
			],
		'public'              => true,
		//'publicly_queryable'  => null, // depends on public
		//'exclude_from_search' => null, // depends on public
		//'show_ui'             => null, // depends on public
		//'show_in_nav_menus'   => null, // depends on public
		//'show_in_menu'        => null, // whether to in admin panel menu
		//'show_in_admin_bar'   => null, // depends on show_in_menu.
		//'show_in_rest'        => null, // Add to REST API. WP 4.7.
		//'rest_base'           => null, // $post_type. WP 4.7.
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-groups',
		//'capability_type'   => 'post',
		//'capabilities'      => 'post', // Array of additional rights for this post type.
		//'map_meta_cap'      => null, // Set to true to enable the default handler for meta caps.
		'hierarchical'        => false,
		// [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats' ]
		'supports'            => [ 'title' ],
		'has_archive'         => true, //false - wp не зарегистрирует урл под тип записей.
		//'rewrite'             => true,
		//'query_var'           => true,
    ] );
  register_post_type( 'schedule', [
        'taxonomies' => [], // post related taxonomies
		'label'  => null,
		'labels' => [
			'name'               => 'Занятия', // name for the post type.
			'singular_name'      => 'Занятие', // name for single post of that type.
			'add_new'            => 'Добавить новое занятие', // to add a new post.
			'add_new_item'       => 'Добавить новое занятие', // title for a newly created post in the admin panel.
			'edit_item'          => 'Редактировать занятие', // for editing post type.
			'new_item'           => 'Новое занятие', // new post's text.
			'view_item'          => 'Смотреть занятие', // for viewing this post type.
			'search_items'       => 'Искать занятие', // search for these post types.
			'not_found'          => 'Не найдено в корзине', // if search has not found anything.
			'not_found_in_trash'          => 'Не найдено', // if search has not found anything.
			'parent_item_colon'  => '', // for parents (for hierarchical post types).
			'menu_name'          => 'Занятия', // menu name.
		],
		'public'              => true,
		//'publicly_queryable'  => null, // depends on public
		//'exclude_from_search' => null, // depends on public
		//'show_ui'             => null, // depends on public
		//'show_in_nav_menus'   => null, // depends on public
		//'show_in_menu'        => null, // whether to in admin panel menu
		//'show_in_admin_bar'   => null, // depends on show_in_menu.
		//'show_in_rest'        => null, // Add to REST API. WP 4.7.
		//'rest_base'           => null, // $post_type. WP 4.7.
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-universal-access-alt',
		//'capability_type'   => 'post',
		//'capabilities'      => 'post', // Array of additional rights for this post type.
		//'map_meta_cap'      => null, // Set to true to enable the default handler for meta caps.
		'hierarchical'        => false,
		// [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats' ]
		'supports'            => [ 'title' ],
		'has_archive'         => true, //false - wp не зарегистрирует урл под тип записей.
		//'rewrite'             => true,
		//'query_var'           => true,
    ] );
  register_taxonomy('schedule_days', ['schedule'], [
        'labels'                => [
            'name'              => 'Дни недели',
            'singular_name'     => 'День недели',
            'search_items'      => 'Найти день недели',
            'all_items'         => 'Все дни недели',
            'view_item '        => 'Посмотреть дни недели',
            'edit_item'         => 'Редактировать дни недели',
            'update_item'       => 'Обновить',
            'add_new_item'      => 'Добавить день недели',
            'new_item_name'     => 'Добавить день недели',
            'menu_name'         => 'Все дни недели',
        ],
        'description'           => '',
        'public'                => true,
        'hierarchical'          => true
    ]);
  register_taxonomy('places', ['schedule'], [
        'labels'                => [
            'name'              => 'Залы',
            'singular_name'     => 'Залы',
            'search_items'      => 'Найти зал',
            'all_items'         => 'Все дни залы',
            'view_item '        => 'Посмотреть зал',
            'edit_item'         => 'Редактировать залы',
            'update_item'       => 'Обновить',
            'add_new_item'      => 'Добавить зал',
            'new_item_name'     => 'Добавить зал',
            'menu_name'         => 'Все залы',
        ],
        'description'           => '',
        'public'                => true,
        'hierarchical'          => true
    ]);
  register_post_type( 'prices', [
        'taxonomies' => [], // post related taxonomies
				'label'  => null,
				'labels' => [
				'name'               => 'Прайс', // name for the post type.
				'singular_name'      => 'Прайс', // name for single post of that type.
				'add_new'            => 'Добавить новый прайс', // to add a new post.
				'add_new_item'       => 'Добавить новый прайс', // title for a newly created post in the admin panel.
				'edit_item'          => 'Редактировать прайс', // for editing post type.
				'new_item'           => 'Новый прайс', // new post's text.
				'view_item'          => 'Смотреть прайс', // for viewing this post type.
				'search_items'       => 'Искать прайс', // search for these post types.
				'not_found'          => 'Не найдено в корзине', // if search has not found anything.
				'not_found_in_trash'          => 'Не найдено', // if search has not found anything.
				'parent_item_colon'  => '', // for parents (for hierarchical post types).
				'menu_name'          => 'Прайсы', // menu name.
			],
			'public'              => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-text-page',
			'hierarchical'        => false,
			'show_in_rest'				=> true, // редактор Гутенберг (editor)
			'supports'            => [ 'title', 'editor' ],
			'has_archive'         => true, //false - wp не зарегистрирует урл под тип записей.

    ] );
  register_post_type( 'cards', [
        'taxonomies' => [], // post related taxonomies
			'label'  => null,
			'labels' => [
			'name'               => 'Карты', // name for the post type.
			'singular_name'      => 'Карта', // name for single post of that type.
			'add_new'            => 'Добавить новая карта', // to add a new post.
			'add_new_item'       => 'Добавить новая карта', // title for a newly created post in the admin panel.
			'edit_item'          => 'Редактировать карту', // for editing post type.
			'new_item'           => 'Новая карта', // new post's text.
			'view_item'          => 'Смотреть карту', // for viewing this post type.
			'search_items'       => 'Искать карту', // search for these post types.
			'not_found'          => 'Не найдено в корзине', // if search has not found anything.
			'not_found_in_trash'          => 'Не найдено', // if search has not found anything.
			'parent_item_colon'  => '', // for parents (for hierarchical post types).
			'menu_name'          => 'Клубные Карты', // menu name.
			],
			'public'              => true,
			//'publicly_queryable'  => null, // depends on public
			//'exclude_from_search' => null, // depends on public
			//'show_ui'             => null, // depends on public
			//'show_in_nav_menus'   => null, // depends on public
			//'show_in_menu'        => null, // whether to in admin panel menu
			//'show_in_admin_bar'   => null, // depends on show_in_menu.
			//'show_in_rest'        => null, // Add to REST API. WP 4.7.
			//'rest_base'           => null, // $post_type. WP 4.7.
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-tickets',
			//'capability_type'   => 'post',
			//'capabilities'      => 'post', // Array of additional rights for this post type.
			//'map_meta_cap'      => null, // Set to true to enable the default handler for meta caps.
			'hierarchical'        => false,
			// [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats' ]
			'supports'            => [ 'title' ],
			'has_archive'         => true, //false - wp не зарегистрирует урл под тип записей.
			//'rewrite'             => true,
			//'query_var'           => true,
			] );

	register_post_type( 'orders', [
      'taxonomies' => [], // post related taxonomies
			'label'  => null,
			'labels' => [
			'name'               => 'Заявки', // name for the post type.
			'singular_name'      => 'Заявка', // name for single post of that type.
			'add_new'            => 'Добавить новую заявку', // to add a new post.
			'add_new_item'       => 'Добавить новую заявку', // title for a newly created post in the admin panel.
			'edit_item'          => 'Редактировать заявку', // for editing post type.
			'new_item'           => 'Новая заявка', // new post's text.
			'view_item'          => 'Смотреть заявку', // for viewing this post type.
			'search_items'       => 'Искать заявку', // search for these post types.
			'not_found'          => 'Не найдено в корзине', // if search has not found anything.
			'not_found_in_trash'          => 'Не найдено', // if search has not found anything.
			'parent_item_colon'  => '', // for parents (for hierarchical post types).
			'menu_name'          => 'Заявки', // menu name.
			],
			'public'              => false,
			'show_ui'							=> true,
			'show_in_menu'				=> true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-format-chat',
			'hierarchical'        => false,
			'supports'            => [ 'title' ],
			'has_archive'         => true, //false - wp не зарегистрирует урл под тип записей.
	] );
}

function si_modal_form_handler(){
	$name = $_POST['si-user-name'] ? $_POST['si-user-name'] : 'Аноним';
	$phone = $_POST['si-user-phone'] ?  $_POST['si-user-phone'] : false;
	$choice = $_POST['form-post-id'] ? $_POST['form-post-id'] : 'empty';
	if( $phone ){
		$name = wp_strip_all_tags( $name );
		$phone = wp_strip_all_tags( $phone );
		$choice = wp_strip_all_tags( $choice );
		$id = wp_insert_post( wp_slash([
			'post_title' => 'Заявка № ',
			'post_type' => 'orders',
			'post_status' => 'publish',
			'meta_input' => [
				'si_order_name' => $name,
				'si_order_phone' => $phone,
				'si_order_choice' => $choice
			]
		]));
		if( $id !== 0 ){
			wp_update_post([
				'ID' => $id,
				'post_title' => 'Заявка № ' . $id
			]); 
			update_field( 'order_status', 'new', $id );
			// можно добавить отправку заявки на почут wp_mail также на api telegram
		}
	}
	
	header( 'Location:' . home_url() );

}

function si_likes(){
	$id = $_POST['id'];
	$todo = $_POST['todo'];;
	$current_data = get_post_meta($id, 'si-like', true);
	$current_data = $current_data ? $current_data : 0;
	if ( $todo === 'plus'){
		$current_data++;
	} else {
		$current_data--;
	}
	$res = update_post_meta( $id, 'si-like', $current_data );
	if($res){
		echo $current_data;
		wp_die();
	} else { 
			wp_die('Лайк не сохранился. Попробуйте еще раз', 500); 
		}
}

function si_like_column( $col_name, $id){
	if ( $col_name !== 'col_likes' ) return;
	$likes = get_post_meta ( $id, 'si-like', true ); 
	echo $likes ? $likes : 0;
}
function si_add_col_likes( $defaults ) {
	$type = get_current_screen();
	if( $type->post_type === 'post' ){
		$defaults['col_likes'] = 'Лайки';
	}
	return $defaults;
}
?>
