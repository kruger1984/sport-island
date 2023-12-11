<?php 

class Si_Widget_Info extends WP_Widget{
    public function __construct(){
        parent::__construct( 
            'si_widget_info',
             'SportIsland - Информация',
             [
                'name' => 'SportIsland - Информация',
                'description' => 'Выводит инфромацию используэться под картой на странице контактов.'
             ]
        );
    }

    public function form( $instance ){
        $vars = [
            'position' => 'Адресс',
            'time' => 'Время',
            'phone' => 'Телефон',
            'mail' => 'Электронный адресс'
        ]
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'id-info' ); ?>">
                Выбирите социальную сеть:
            </label>
            <input 
                id="<?php echo $this->get_field_id( 'id-info' ); ?>"
                type="text"
                name="<?php echo $this->get_field_name('info'); ?>"
                value="<?php
                $info_w = !empty( $instance['info'] ) ? $instance['info'] : '';
                echo $info_w ; ?>"
                class="widefat"
            >
        </p>  
        <p>
            <label for="<?php echo $this->get_field_id( 'id-var' ); ?>">
                Выбирите социальную сеть:
            </label>
            <select 
                id="<?php echo $this->get_field_id( 'id-var' ); ?>"
                name="<?php echo $this->get_field_name('var'); ?>"
                class="widefat"
            >
            <?php 
                foreach ( $vars as $var => $desc ) :
            ?>
            <option value="<?php echo $var?>"
                    <?php selected( $instance['var'], $var, true ) ?>
            >
                <?php echo $desc ?>
            </option>
            <?php endforeach; ?>

            </select>
        </p>      
    <?php   
    }
    public function widget( $args, $instance ){
        switch ( $instance['var']){
            case 'position':
?>
                <span class="widget-address">
                    <?php echo $instance['info']; ?>
                </span>
<?php
                break;
            case 'time':
?>
                <span class="widget-working-time">
                    <?php echo $instance['info']; ?>
                </span>
<?php
                break;
            case 'phone':
                $tel = preg_replace( '#[^+0-9/]#', '', $instance['info']);
?>
                <a href="<?php echo $tel; ?>" class="widget-phone">
                    <?php echo $instance['info']; ?>
                </a>
<?php
                break;
            case 'mail':
?>
                <a href="mailto:<?php echo $instance['info']; ?>" class="widget-email">
                    <?php echo $instance['info']; ?>
                </a>
<?php
                break;
            default: echo '';
            break;
        }
    }
    public function update( $new_instance, $old_instance ){
        return $new_instance;
    }

}

?>
