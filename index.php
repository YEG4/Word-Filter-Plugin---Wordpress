<?php  

/**
 * Plugin Name: Word Filter
 * Description: Replaces a list of words
 * Version: 1.0
 * Author: Ahmad Tarek
 * Author URI: https://linkedin.com/in/at8oneim
 */

 if(! defined('ABSPATH')) exit;

 class WordFilterPlugin{
    public function __construct() {
        add_action('admin_menu', array( $this,'ourMenu') );
        add_action('admin_init', array($this, 'filterOptions'));
        if(get_option('plugin_words_to_filter'))  add_filter('the_content', array($this, 'filterLogic'));
    }

    function filterOptions(){
        add_settings_section('replacement-text-section', null, null, 'word-filter-options');
        register_setting('replacementFields', 'replacement_text');
        add_settings_field('replacement-text','Filtered Text', array($this, 'replacementFieldHTML'), 'word-filter-options', 'replacement-text-section');
    }

    function replacementFieldHTML(){?>
        <input type="text" name="replacement_text" value="<?php echo esc_attr(get_option('replacement_text', '***')) ?>">
        <p class="description">Leave blank to simply remove the filtered words.</p>
    <?php }

    function filterLogic($content){
        $badWords = explode(',', get_option('plugin_words_to_filter'));
        $badWordsTrimmed = array_map('trim', $badWords);
        return str_ireplace($badWordsTrimmed, esc_html(get_option('replacement_text', '***')), $content);
    }


    function ourMenu(){
        // Adds a Menu on the left inside Admin page on the sidebar and the method that will render html for this plugin and where to put the menu on the sidebar.
        $mainPagehook = add_menu_page('Words To Filter', 'Word Filter', 'manage_options', 'wordfilter', array($this, 'wordFilterPage'), 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMCAyMEMxNS41MjI5IDIwIDIwIDE1LjUyMjkgMjAgMTBDMjAgNC40NzcxNCAxNS41MjI5IDAgMTAgMEM0LjQ3NzE0IDAgMCA0LjQ3NzE0IDAgMTBDMCAxNS41MjI5IDQuNDc3MTQgMjAgMTAgMjBaTTExLjk5IDcuNDQ2NjZMMTAuMDc4MSAxLjU2MjVMOC4xNjYyNiA3LjQ0NjY2SDEuOTc5MjhMNi45ODQ2NSAxMS4wODMzTDUuMDcyNzUgMTYuOTY3NEwxMC4wNzgxIDEzLjMzMDhMMTUuMDgzNSAxNi45Njc0TDEzLjE3MTYgMTEuMDgzM0wxOC4xNzcgNy40NDY2NkgxMS45OVoiIGZpbGw9IiNGRkRGOEQiLz4KPC9zdmc+', 100);
        add_submenu_page('wordfilter', 'Words To Filter', 'Words List', 'manage_options', 'wordfilter', array($this, 'wordFilterPage'));
        add_submenu_page('wordfilter', 'Word Filter Options', 'Options', 'manage_options', 'word-filter-options', array($this, 'optionsSubPage'));

        add_action("load-{$mainPagehook}", array($this, 'mainPageAssets'));
        }
    
    function mainPageAssets(){
        wp_enqueue_style('filterAdminCss', plugin_dir_url(__FILE__) . 'styles.css');
    }

    function handleForm(){
       if(wp_verify_nonce(isset($_POST['ourNonce']) ? $_POST['ourNonce'] : '', 'saveFilterWords') AND current_user_can('manage_options')){
        update_option('plugin_words_to_filter', sanitize_text_field($_POST['plugin_words_to_filter'])); ?>
        <div class="updated"> <!-- This class name is used by wordpress to style it  -->
            <p>Your filtered words were saved.</p>
        </div>
    <?php }else{ ?>
        
            <div class="error">
                <p>Sorry, you do not have persmission to perform that action.</p>
            </div>
    <?php }
     }
    
    /* Output HTML Content for the filter page */
    function wordFilterPage(){ ?>
        <div class="wrap">
            <h1>Word Filter</h1>
            <?php if(isset($_POST['justsubmitted']) == "true") $this->handleForm();?>
            <form method="POST">
                <input type="hidden" name="justsubmitted" value="true">
                <?php wp_nonce_field('saveFilterWords', 'ourNonce') ?>
                <label for="plugin_words_to_filter"><p>Enter a <strong>comma-separated</strong> list of words to filter from your site's content</p></label>
                <div class="word-filter__flex-container">
                    <textarea name="plugin_words_to_filter" id="plugin_words_to_filter" placeholder="bad, mean, awful, horrible"><?php echo esc_textarea( get_option('plugin_words_to_filter')) ?></textarea>
                </div>
                <input type="submit" value="Save Changes" name="submit" id="submit" class="button button-primary">
            </form>
        </div>
    <?php }

    
    /* Output HTML Content for the filter options  subpage */
    function optionsSubPage(){ ?>
        <div class="wrap">
            <h1>Word Filter Options</h1>
            <form action="options.php" method="POST"> <!-- This is a generated wordpress form. wordpress takes care of outputting elements and sections i register. -->
                <?php settings_errors(); // this will display the success message when the save button is clicked. ?> 
                <?php settings_fields('replacementFields') ?>
                <?php do_settings_sections('word-filter-options') ?>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php }
 }

 $wordFilterPlugin = new WordFilterPlugin();



