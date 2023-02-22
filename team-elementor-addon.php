<?php
/**
 * Plugin Name: Elementor Addon by TorontoDigits
 * Description: This Addon Creats a Elementor Widget that displays teams.
 * Version:     1.0.0
 * Author:      TorontoDigits
 * Author URI:  https://torontodigits.com/
 * Text Domain: toronto-digits
 */

function register_teamwidget($widgets_manager)
{

    require_once (__DIR__ . '/widgets/teamwidget.php');

    $widgets_manager->register(new \Team_Widget());

}
add_action('elementor/widgets/register', 'register_teamwidget');

function team_style()
{
    wp_enqueue_script('team-widget', plugins_url('jQuery/team-widget.js', __FILE__) , array() , '1.0.0');

    wp_enqueue_style('team-widget', plugins_url('css/team-widget.css', __FILE__) , array() , '1.0.0');

}
add_action('wp_enqueue_scripts', 'team_style');

// Registering Custom Post type for Teams


function td_team_post_type()
{
    $args = array(
        'labels' => array(
            'name' => 'Teams',
            'singular_name' => 'Team',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Team',
            'edit_item' => 'Edit Team',
            'new_item' => 'New Team',
            'all_items' => 'All Teams',
            'view_item' => 'View Team',
            'search_items' => 'Search Teams',
            'not_found' => 'No teams found',
            'not_found_in_trash' => 'No teams found in Trash',
            'parent_item_colon' => '',
            'menu_name' => 'Teams'
        ) ,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'teams'
        ) ,
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_icon' => 'dashicons-groups',
        'menu_position' => null,
        'supports' => array(
            'title',
            'editor',
            'author',
            'thumbnail',
            'excerpt',
            'comments'
        )
    );
    register_post_type('team', $args);
}
add_action('init', 'td_team_post_type');

// Registering Taxonomy for
function create_league_taxonomy()
{
    register_taxonomy('league', 'team', array(
        'label' => __('Leagues') ,
        'rewrite' => array(
            'slug' => 'leagues'
        ) ,
        'hierarchical' => true,
    ));
}
add_action('init', 'create_league_taxonomy');

/**
 * Plugin class
 *
 */
if (!class_exists('CT_TAX_META'))
{

    class CT_TAX_META
    {

        public function __construct()
        {
            //
            
        }

        /*
         * Initialize the class and start calling our hooks and filters
        */
        public function init()
        {
            add_action('league_add_form_fields', array($this,'add_category_image') , 10, 2);
            add_action('created_league', array($this,'save_category_image') , 10, 2);
            add_action('league_edit_form_fields', array($this,'update_category_image') , 10, 2);add_action('edited_league', array($this,'updated_category_image') , 10, 2);
            add_action('admin_enqueue_scripts', array($this,'load_media'));
            add_action('admin_footer', array($this,'add_script'));
        }

        public function load_media()
        {
            wp_enqueue_media();
        }

        /*
         * Add a form field in the new category page
        */
        public function add_category_image($taxonomy)
        { ?>
      <div class="form-field term-group">
        <label for="category-image-id">
          <?php _e('Image', 'hero-theme'); ?>
        </label>
        <input type="hidden" id="category-image-id" name="category-image-id" class="custom_media_url" value="">
        <div id="category-image-wrapper"></div>
        <p>
          <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button"
            name="ct_tax_media_button" value="<?php _e('Add Image', 'hero-theme'); ?>" />
          <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove"
            name="ct_tax_media_remove" value="<?php _e('Remove Image', 'hero-theme'); ?>" />
        </p>
      </div>
      <?php
        }

        /*
         * Save the form field
        */
        public function save_category_image($term_id, $tt_id)
        {
            if (isset($_POST['category-image-id']) && '' !== $_POST['category-image-id'])
            {
                $image = $_POST['category-image-id'];
                add_term_meta($term_id, 'category-image-id', $image, true);
            }
        }

        /*
         * Edit the form field
        */
        public function update_category_image($term, $taxonomy)
        { ?>
      <tr class="form-field term-group-wrap">
        <th scope="row">
          <label for="category-image-id">
            <?php _e('Image', 'hero-theme'); ?>
          </label>
        </th>
        <td>
          <?php $image_id = get_term_meta($term->term_id, 'category-image-id', true); ?>
          <input type="hidden" id="category-image-id" name="category-image-id" value="<?php echo $image_id; ?>">
          <div id="category-image-wrapper">
            <?php if ($image_id)
            { ?>
              <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
            <?php
            } ?>
          </div>
          <p>
            <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button"
              name="ct_tax_media_button" value="<?php _e('Add Image', 'hero-theme'); ?>" />
            <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove"
              name="ct_tax_media_remove" value="<?php _e('Remove Image', 'hero-theme'); ?>" />
          </p>
        </td>
      </tr>
      <?php
        }

        /*
         * Update the form field value
        */
        public function updated_category_image($term_id, $tt_id)
        {
            if (isset($_POST['category-image-id']) && '' !== $_POST['category-image-id'])
            {
                $image = $_POST['category-image-id'];
                update_term_meta($term_id, 'category-image-id', $image);
            }
            else
            {
                update_term_meta($term_id, 'category-image-id', '');
            }
        }

        /*
         * Add script
        */
        public function add_script()
        { ?>
      <script>
        jQuery(document).ready(function ($) {
          function ct_media_upload(button_class) {
            var _custom_media = true,
              _orig_send_attachment = wp.media.editor.send.attachment;
            $('body').on('click', button_class, function (e) {
              var button_id = '#' + $(this).attr('id');
              var send_attachment_bkp = wp.media.editor.send.attachment;
              var button = $(button_id);
              _custom_media = true;
              wp.media.editor.send.attachment = function (props, attachment) {
                if (_custom_media) {
                  $('#category-image-id').val(attachment.id);
                  $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                  $('#category-image-wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block');
                } else {
                  return _orig_send_attachment.apply(button_id, [props, attachment]);
                }
              }
              wp.media.editor.open(button);
              return false;
            });
          }
          ct_media_upload('.ct_tax_media_button.button');
          $('body').on('click', '.ct_tax_media_remove', function () {
            $('#category-image-id').val('');
            $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
          });
          $(document).ajaxComplete(function (event, xhr, settings) {
            var queryStringArr = settings.data.split('&');
            if ($.inArray('action=add-tag', queryStringArr) !== -1) {
              var xml = xhr.responseXML;
              $response = $(xml).find('term_id').text();
              if ($response != "") {
                // Clear the thumb image
                $('#category-image-wrapper').html('');
              }
            }
          });
        });
      </script>
    <?php
        }

    }

    $CT_TAX_META = new CT_TAX_META();
    $CT_TAX_META->init();

}

