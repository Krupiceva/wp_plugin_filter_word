<?php

/*

Plugin Name: Krupiceva Word Filter Plugin
Description: Replaces a list of words
Version: 1.0
Author: Krupiceva

*/

if( ! defined('ABSPATH')) exit; //Exit if accessed directly

class KrupicevaWordFilterPlugin{
    function __construct(){
        add_action('admin_menu', array($this, 'ourMenu'));
        if(get_option('plugin_words_to_filter')){
            add_filter('the_content', array($this, 'filterLogic'));
        }
        add_action('admin_init', array($this, 'ourSettings'));
    }


    //Create menu in admin for this plugin
    function ourMenu(){
        $mainPageHook = add_menu_page('Words To Filter', 'Word Filter', 'manage_options', 'krupicevawordfilter', array($this, 'wordFilterPage'), 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMCAyMEMxNS41MjI5IDIwIDIwIDE1LjUyMjkgMjAgMTBDMjAgNC40NzcxNCAxNS41MjI5IDAgMTAgMEM0LjQ3NzE0IDAgMCA0LjQ3NzE0IDAgMTBDMCAxNS41MjI5IDQuNDc3MTQgMjAgMTAgMjBaTTExLjk5IDcuNDQ2NjZMMTAuMDc4MSAxLjU2MjVMOC4xNjYyNiA3LjQ0NjY2SDEuOTc5MjhMNi45ODQ2NSAxMS4wODMzTDUuMDcyNzUgMTYuOTY3NEwxMC4wNzgxIDEzLjMzMDhMMTUuMDgzNSAxNi45Njc0TDEzLjE3MTYgMTEuMDgzM0wxOC4xNzcgNy40NDY2NkgxMS45OVoiIGZpbGw9IiNGRkRGOEQiLz4KPC9zdmc+Cg==', 100 );
        add_submenu_page('krupicevawordfilter', 'Word To Filter', 'Words List', 'manage_options', 'krupicevawordfilter', array($this, 'wordFilterPage'));
        add_submenu_page('krupicevawordfilter', 'Word Filter Options', 'Options', 'manage_options', 'word-filter-options', array($this, 'optionsSubPage'));
        add_action("load-{$mainPageHook}", array($this, 'mainPageAssets'));
    }


    //HTML content for Word Filter Page
    function wordFilterPage(){ ?>
        <div class="wrap">
            <h1>Word Filter</h1>
            <?php 
                if($_POST['justsubmitted'] == true){
                    $this->handleForm();
                }
            ?>
            <form method="POST">
                <input type="hidden" name="justsubmitted" value="true">
                <?php  
                    wp_nonce_field('saveFilterWords', 'ourNonce');
                ?>
                <label for="plugin_words_to_filter">
                    <p>Enter a <strong>comma-separated</strong> list of words to filter from your site's content</p>
                </label>
                <div class="word-filter__flex-container">
                    <textarea name="plugin_words_to_filter" id="plugin_words_to_filter" placeholder="bad, mean, awful, horrible"><?php echo esc_textarea(get_option('plugin_words_to_filter')); ?></textarea>
                </div>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </form>
        </div>
    <?php }


    //HTML content for Word Filter Options Page
    function optionsSubPage(){ ?>
        <div class="wrap">
            <h1>Word Filter Options</h1>
            <form action="options.php" method="POST">
                <?php
                    settings_errors();
                    settings_fields('replacmentFields');
                    do_settings_sections('word-filter-options');
                    submit_button(); 
                ?>
            </form>
        </div>
    <?php }

    //Register new fields for options page
    function ourSettings(){
        add_settings_section('replacment-text-section', null, null, 'word-filter-options');
        register_setting('replacmentFields', 'replacmentText');
        add_settings_field('replacment-text', 'Filtered Text', array($this, 'replacmentFieldHTML'), 'word-filter-options', 'replacment-text-section');
    }

    //HTML for replacment text field on options page
    function replacmentFieldHTML(){ ?>
        <input type="text" name="replacmentText" value="<?php echo esc_attr(get_option('replacmentText', '***')); ?>">
        <p class="description">Leave blank to simply remove filtered words.</p>
    <?php }


    //Custom assets for plugin, custom css
    function mainPageAssets(){
        wp_enqueue_style('filterAdminCSS', plugin_dir_url(__FILE__) . 'styles.css');
    }

    //Function that get execute when user click Save Changes button (submit button)
    function handleForm(){
        if(wp_verify_nonce($_POST['ourNonce'], 'saveFilterWords') AND current_user_can('manage_options')){
            update_option('plugin_words_to_filter', sanitize_text_field($_POST['plugin_words_to_filter'])); ?>
            <div class="updated">
                <p>Your filter words were saved.</p>
            </div>
        <?php } else { ?>
            <div class="error">
                <p>Sorry you do not have permission to perform that action.</p>
            </div>
        <?php }
    }

    //Logic for filtering and removing disared words from content blog text
    function filterLogic($content){
        $badWords = explode(',' , get_option('plugin_words_to_filter'));
        $badWordsTrimmed = array_map('trim', $badWords);
        return str_ireplace($badWordsTrimmed, esc_html(get_option('replacmentText', '***')), $content);
    }

}

$krupicevaWordFilterPlugin = new KrupicevaWordFilterPlugin();

?>