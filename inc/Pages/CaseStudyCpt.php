<?php


/**
 * @package CaseStudyPlugin
 */

namespace Inc\Pages;

use Inc\Api\CptApi;
use \Inc\Base\BaseController;
// use \Inc\Api\Callbacks\RestApiCallbacks;

class CaseStudyCpt extends BaseController
{

  public $case_study_cpt;
  public $cpt_settings = array();
  public $callbacks;
  public $postType = "case_study";

  public function register()
  {
    // https://severalnines.com/case-studies/?cat=hosting&tax=case_study_industry
    // Single Post Slug: http://localhost:9000/?case-study=test-case-study
    // Taxonomy Post Slug: http://localhost:9000/?case-study=test-case-study
    // $this->callbacks = new RestApiCallbacks();
    $this->case_study_cpt = new CptApi();
    $this->cpt_settings = [
      [
        'post_type' => $this->postType, // keep it unique
        'menu_name' => 'Case Study',
        'singular_name' => 'Case Study',
        'menu_icon' => 'dashicons-admin-plugins',
        'query_var' => 'case-study'
      ]
    ];

    $this->tax_settings = [
      [
        "tax_title" => "Industry",
        "tax_slug" => "industry"
      ],
      [
        "tax_title" => "Technology",
        "tax_slug" => "technology"
      ],
      [
        "tax_title" => "Product",
        "tax_slug" => "product"
      ]
    ];

    $this->sample_settings = [
      'create' => false,
      'post_count' => 3,
      'description_length' => 2,
      'post_type' => $this->postType,
      'taxonomy_status' => false
    ];

    $this->case_study_cpt->addCpt($this->cpt_settings)->WithTaxonomy($this->tax_settings)->WithSamplePost($this->sample_settings)->register();
  }
}