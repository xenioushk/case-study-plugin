<?php


/**
 * @package CaseStudyPlugin
 */

namespace Inc\Api;

use \Inc\Base\BaseController;
use joshtronic\LoremIpsum;

// CptApi's interface class.

class CptApi extends BaseController

{

  public $cpt_settings = [];
  public $tax_settings = [];
  public $sample_settings = [];

  public function register()
  {
    if (!empty($this->cpt_settings)) {
      add_action('init', [$this, 'addCustomCptApi']);
    }
  }

  public function addCpt(array $cpt_settings)
  {
    $this->cpt_settings = $cpt_settings;
    return $this;
  }

  public function WithTaxonomy(array $tax_settings = [])
  {

    if (empty($this->cpt_settings) || empty($tax_settings))
      return $this;

    /* 
    * Get the parent post type.
    */

    $cpt_info = $this->cpt_settings[0];

    foreach ($tax_settings as $tax) {

      $this->tax_settings[] = [

        $tax['tax_slug'],
        [$cpt_info['post_type']],
        [
          'label' => $tax['tax_title'] ?? __('Taxonomy Title', $this->plugin_slug),
          'hierarchical' => true,
          'rewrite' => ['slug' => $cpt_info['post_type'] . '-' . $tax['tax_slug']],
          'show_admin_column' => true,
          'show_in_rest' => true,
          'labels' => [
            'singular_name' => $tax['tax_title'],
            'all_items' => __('All', $this->plugin_slug) . $tax['tax_title'],
            'edit_item' => __('Edit', $this->plugin_slug) . ' ' . $tax['tax_title'],
            'view_item' => __('View', $this->plugin_slug) . ' ' . $tax['tax_title'],
            'update_item' => __('Update', $this->plugin_slug) . ' ' . $tax['tax_title'],
            'add_new_item' => __('Add New', $this->plugin_slug) . ' ' . $tax['tax_title'],
            'new_item_name' => __('New Title', $this->plugin_slug),
            'search_items' => __('Search', $this->plugin_slug) . ' ' . $tax['tax_title'],
            'popular_items' => __('Popular', $this->plugin_slug) . ' ' . $tax['tax_title'],
            'separate_items_with_commas' => __('Separate with comma', $this->plugin_slug),
            'choose_from_most_used' => __('Choose from most used', $this->plugin_slug) . ' ' . $tax['tax_title'],
            'not_found' => __('Nothing found', $this->plugin_slug),
          ]
        ]
      ];
    }

    return $this;
  }

  public function WithSamplePost(array $sample_settings = [])
  {

    if (empty($sample_settings) || $sample_settings['create'] != true || empty($sample_settings['post_type']) || $sample_settings['post_count'] <= 0)
      return $this;

    $this->sample_settings = $sample_settings;

    return $this;
  }

  public function addCustomCptApi()
  {

    /* 
    * Register custom post type.   
    */

    foreach ($this->cpt_settings as $cpt) {

      $labels = array(
        'name' => esc_html__('All', $this->plugin_slug) . ' ' . $cpt['menu_name'],
        'singular_name' => $cpt['singular_name'] ?? $cpt['menu_name'],
        'add_new' => esc_html__('Add New', $this->plugin_slug)  . ' ' . $cpt['menu_name'],
        'add_new_item' => esc_html__('Add New', $this->plugin_slug),
        'edit_item' => esc_html__('Edit', $this->plugin_slug) . ' ' . $cpt['menu_name'],
        'new_item' => esc_html__('New', $this->plugin_slug) . ' ' . $cpt['menu_name'],
        'all_items' => esc_html__('All', $this->plugin_slug) . ' ' . $cpt['menu_name'],
        'view_item' => esc_html__('View', $this->plugin_slug) . ' ' . $cpt['menu_name'],
        'search_items' => esc_html__('Search', $this->plugin_slug) . ' ' . $cpt['menu_name'],
        'not_found' => esc_html__('Not found', $this->plugin_slug) . ' ' . $cpt['menu_name'],
        'not_found_in_trash' => esc_html__('Not found in trash', $this->plugin_slug) . ' ' . $cpt['menu_name'],
        'parent_item_colon' => '',
        'menu_name' => $cpt['menu_name']
      );


      $args = array(
        'labels' => $labels,
        'query_var' => $cpt['query_var'] ?? $cpt['post_type'],
        'show_in_nav_menus' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'rewrite' => array(
          'slug' => $cpt['slug'] ?? $cpt['post_type'],
          'with_front' => true //before it was true
        ),
        'publicly_queryable' => true, // turn it to false, if you want to disable generate single page
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => true,
        'show_in_admin_bar' => true,
        'show_in_rest' => true,
        'supports' =>  $cpt['supports'] ??  array('title', 'editor', 'revisions', 'author', 'thumbnail'),
        'menu_icon' => $cpt['menu_icon'] ?? 'dashicons-media-document'
      );

      register_post_type($cpt['post_type'], $args);
    }

    /* 
    *  Register all the taxonomies.
    */

    if (!empty($this->tax_settings)) {
      foreach ($this->tax_settings as $tax) {
        register_taxonomy($tax[0], $tax[1], $tax[2]);
      }
    }

    /* 
    *  Add/Create sample posts;
    */


    if (!empty($this->sample_settings)) {

      $runtime = "bwl_sample";
      if (get_option('bwl_sample_posts_gen') != $runtime) {
        $updated = update_option('bwl_sample_posts_gen', $runtime);
        if ($updated === true) {
          $this->createSamplePost();
        }
      } else {
        delete_option('bwl_sample_posts_gen');
      }
    }
  }

  private function createSamplePost()
  {

    $sample_settings = $this->sample_settings;
    $lipsum = new LoremIpsum();
    // echo "<pre>";
    // print_r($post);
    // echo "</pre>";

    $post_count = $sample_settings['post_count'];
    $title_length = $sample_settings['title_length'] ?: rand(2, 7);
    $description_length = $sample_settings['description_length'] ?: 2;
    $post_type = $sample_settings['post_type'];
    $post_status = $sample_settings['status'] ?: 'publish';
    $taxonomy_status = $sample_settings['taxonomy_status'] ?: false;

    for ($count = 0; $count < $post_count; $count++) {

      $post_data = [
        'post_title' => $lipsum->words($title_length),
        'post_content' =>  $lipsum->paragraphs($description_length),
        'post_type' =>  $post_type,
        'post_status' => $post_status
      ];
      $new_post_id = wp_insert_post($post_data);
      // Assign post to a particular category. 

      if (!is_wp_error($new_post_id) && $taxonomy_status == true) {

        // set category.

        // $job_cat_taxonomy = "category";
        // $job_category = get_term_by('id', $data->get_param('taskCategory'), $job_cat_taxonomy);
        // wp_set_object_terms($new_post_id, $job_category->slug, $job_cat_taxonomy, true);
      }
    }
  }
}