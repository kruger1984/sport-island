<?php 

class Si_Widget_Contacts extends WP_Widget{
    public function __construct(){
        parent::__construct( 
            'si_widget_contacts',
             'SportIsland - Виджет с контактами',
             [
                'name' => 'SportIsland - Виджет с контактами',
                'description' => 'Выводит телефон и адрес'
             ]
        );
    }

    public function form( $instance ){
    ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'id-phone' ); ?>">
                Введите телефон:
            </label>
            <input 
                id="<?php echo $this->get_field_id( 'id-phone' ); ?>"
                type="text"
                name="<?php echo $this->get_field_name('phone'); ?>"
                value="<?php
                $text = !empty( $instance['phone'] ) ? $instance['phone'] : '';
                echo $text; ?>"
                class="widefat"
            >
        </p> 
        <p>
            <label for="<?php echo $this->get_field_id( 'id-address' ); ?>">
                Введите текст:
            </label>
            <input 
                id="<?php echo $this->get_field_id( 'id-address' ); ?>"
                type="text"
                name="<?php echo $this->get_field_name('address'); ?>"
                value="<?php
                $text = !empty( $instance['address'] ) ? $instance['address'] : '';
                echo $text; ?>"
                class="widefat"
            >
        </p>         
    <?php   
    }
    public function widget( $args, $instance ){
        $tel_text = $instance['phone'];
        $pattern = '#[^+0-9/]#';
        $tel = preg_replace($pattern, '', $tel_text);
    ?>
        <address class="main-header__widget widget-contacts">
          <a href="tel:<?php echo $tel?>" class="widget-contacts__phone"> 
            <?php echo $instance['phone'] ?>
          </a>
          <p class="widget-contacts__address"> 
            <?php echo $instance['address'] ?>
          </p>
        </address>

    <?php
    }
    public function update( $new_instance, $old_instance ){
        return $new_instance;
    }

}

?>
