<?php
/*
Plugin Name: meetup-api
Plugin URI: http://www.2sonder.com/meetup
Description: Complete meetup API implementation
Author: Bart Breunesse, Noud Roosendaal
Version: 1.0
Author URI: http://www.2sonder.com/meetup
*/

include 'bootstrap.php';
require_once __DIR__.'/library/meetupAdapter.php';
add_action( 'admin_menu', 'meetup_menu' );

/** Step 1. */
function meetup_menu() {
	//add_options_page( 'My Plugin Options', 'My Plugin', 'manage_options', 'my-unique-identifier', 'my_plugin_options' );
    add_menu_page( 'Meetup', 'Meetup', 'manage_options', 'meetup api', 'test_init' );
}

/** Step 3. */
function test_init(){

	import_events();

	echo '<p>Klik hier om de events bij te werken.</p><p>Vul na de import deze events nog handmatig aan. Dit kan <a href="/wp-admin/edit.php?post_type=event">hier.</a></p>
	<div class="update-events">
		<form method="POST" action="">
			<input type="hidden" name="update" value="wpm-update-events" />
			<input type="submit" value="Evenementen bijwerken" class="button">
		</form>
	</div>';
}


function import_events() {

	if(isset($_POST['update'])) {

		//first get past events
		$mu = new meetupAdapter();

		$past_events = $mu->getEvents('past', null);
		$upcoming_events = $mu->getEvents('upcoming', null);

		foreach($past_events->results AS $key => $value) {
			_insert_events($value);
		}
        echo '<pre>'.print_r($upcoming_events,1).'</pre>';
        if(is_array($upcoming_events->results) && !empty($upcoming_events->results)) {
            foreach ($upcoming_events->results AS $key => $value) {
                _insert_events($value);
            }
        }

		//echo '<pre>'.print_r($events,1).'</pre>';
	}
}

function _insert_events($event){
	//update_field
	$post_id = post_exists($event->name);
	if(!$post_id) {
		$params = array(
            'post_status' => 'publish',
			'post_name' => $event->name,
			'post_type' => 'event',
            'post_title' => $event->name
		);
		$post_id = wp_insert_post($params);
		if(is_numeric($post_id)) {
			update_field('meetup_id', $event->id, $post_id);
			update_field('titel', $event->name, $post_id);
			update_field('description', $event->description, $post_id);
            echo '<p>Evenement: \''.$event->name. '\' is geimporteerd.</p>';
			//update_field();
			//update_field();
		}
	}
}

add_action('init', 'register_custom_menu');

function eventRegister()
{
	$labels = array(
		'name' => _x('Events', 'post type general name'),
		'singular_name' => _x('Event', 'post type singular name'),
		'add_new' => _x('Add event', 'event item'),
		'add_new_item' => __('Add New event'),
		'edit_item' => __('Edit event'),
		'new_item' => __('New event'),
		'view_item' => __('View event'),
		'search_items' => __('Search event'),
		'not_found' => __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array('title', 'editor', 'thumbnail'),
		'rewrite' => true,
		'show_in_nav_menus' => true,
	);

	register_post_type('event' , $args);
}

add_action('init', 'eventRegister');


function stellingRegister()
{
	$labels = array(
		'name' => _x('Stellingen', 'post type general name'),
		'singular_name' => _x('Stelling', 'post type singular name'),
		'add_new' => _x('Add stelling', 'event item'),
		'add_new_item' => __('Add New stelling'),
		'edit_item' => __('Edit stelling'),
		'new_item' => __('New stelling'),
		'view_item' => __('View stelling'),
		'search_items' => __('Search stelling'),
		'not_found' => __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array('title', 'editor', 'thumbnail'),
		'rewrite' => true,
		'show_in_nav_menus' => true,
	);
	register_post_type('stelling' , $args);
}

add_action('init', 'stellingRegister');

function reactieRegister()
{
	$labels = array(
		'name' => _x('Reacties', 'post type general name'),
		'singular_name' => _x('Reactie', 'post type singular name'),
		'add_new' => _x('Add reactie', 'event item'),
		'add_new_item' => __('Add New reactie'),
		'edit_item' => __('Edit reactie'),
		'new_item' => __('New reactie'),
		'view_item' => __('View reactie'),
		'search_items' => __('Search reactie'),
		'not_found' => __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array('title', 'editor', 'thumbnail'),
		'rewrite' => true,
		'show_in_nav_menus' => true,
	);

	register_post_type('reactie' , $args);
}

add_action('init', 'reactieRegister');

function importPosts() {

	$mu = new meetupAdapter('apikey');

	global $user_ID;
	$new_post = array(
		'post_title' => 'My New Post',
		'post_status' => 'publish',
		'post_date' => date('Y-m-d H:i:s'),
		'post_author' => $user_ID,
		'post_type' => 'post',
		'post_category' => array(0)
	);
	$post_id = wp_insert_post($new_post);

}

/*
function my_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p>';
	echo '</div>';
}*/