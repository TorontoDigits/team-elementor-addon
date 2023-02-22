<?php
use Elementor\Controls_Manager;

class Team_Widget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'team';
    }

    public function get_title()
    {
        return __('Team', 'toronto-digits');
    }

    public function get_icon()
    {
        return 'dashicons-buddicons-buddypress-logo';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function _register_controls() {
      $this->start_controls_section(
        'team_section',
        [
          'label' => __( 'Team Options', 'toronto-digits' ),
          'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]
      );
  
      $this->add_control(
        'number_of_teams',
        [
          'label' => __( 'Number of Teams to Show', 'toronto-digits' ),
          'type' => \Elementor\Controls_Manager::NUMBER,
          'min' => 1,
          'max' => 10,
          'default' => 5,
        ]
      );
  
      $categories = get_categories( [ 'taxonomy' => 'league' ] );
      $options = [];
  
      foreach ( $categories as $category ) {
        $options[ $category->term_id ] = $category->name;
      }
  
      $this->add_control(
        'team_category',
        [
          'label' => __( 'Team Category', 'toronto-digits' ),
          'type' => \Elementor\Controls_Manager::SELECT,
          'options' => $options,
          'default' => 0,
        ]
      );
  
      $this->end_controls_section();
  
      // Style Section
      $this->start_controls_section(
        'style_section',
        [
          'label' => __( 'Style', 'toronto-digits' ),
          'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]
      );
  
      $this->add_control(
        'team_name_color',
        [
          'label' => __( 'Team Name Color', 'toronto-digits' ),
          'type' => \Elementor\Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .team-name' => 'color: {{VALUE}}',
          ],
        ]
      );
  
      $this->add_control(
        'team_name_font_size',
        [
          'label' => __( 'Team Name Font Size', 'toronto-digits' ),
          'type' => \Elementor\Controls_Manager::SLIDER,
          'size_units' => [ 'px', 'em', '%' ],
          'range' => [
            'px' => [
              'min' => 12,
              'max' => 40,
              'step' => 1,
            ],
            'em' => [
              'min' => 0.5,
              'max' => 2,
              'step' => 0.1,
            ],
            '%' => [
              'min' => 50,
              'max' => 150,
              'step' => 1,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .team-name' => 'font-size: {{SIZE}}{{UNIT}}',
          ],
        ]
      );
  
      $this->end_controls_section();
    }
  

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $category_id = $settings['team_category'];
    
        $image_id = get_term_meta($category_id, 'category-image-id', true); // get the ID of the image field for the selected category
        $image = wp_get_attachment_image($image_id, 'full'); // get the full image size for the selected category
        $query_args = ['post_type' => 'team', 'posts_per_page' => $settings['number_of_teams'], 'tax_query' => [['taxonomy' => 'league', 'field' => 'term_id', 'terms' => $category_id, ], ], ];
    
        $query = new WP_Query($query_args);
        ?>
        <div class="team-widget">
          <?php echo $image; ?>
          <ul>
            <?php while ($query->have_posts()):
                $query->the_post(); ?>
              <div class="team-info">
                <h4 class="team-name" style="font-size: <?php echo $settings['team_name_font_size']; ?>px;"><span style="color: <?php echo $settings['team_name_color']; ?>"><?php the_title(); ?></span></h4>
                <h4><?php the_content(); ?></h4>
    
                <p><?php echo get_post_meta(get_the_ID() , 'team_history', true); ?></p>
                <?php $team_logo = get_post_meta(get_the_ID() , 'team_logo', true); ?>
                <?php if (!empty($team_logo)): ?>
                  <img src="<?php echo $team_logo; ?>" alt="<?php the_title(); ?> Logo" />
                <?php
                endif; ?>
                <p><?php echo get_post_meta(get_the_ID() , 'team_nickname', true); ?></p>
              </div>
            <?php
            endwhile;
            wp_reset_postdata(); ?>
          </ul>
        </div>
        <?php
    }
  }    