<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'position' ) ); ?>"><?php echo esc_html( __( 'Advertisement Position', 'awpcp-campagin-manager' ) ); ?></label>
    <select id="<?php echo esc_attr( $this->get_field_id( 'position' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'position' ) ); ?>">
        <option value="sidebar-one"<?php echo $instance['position'] == 'sidebar-one' ? ' selected="selected"' : ''; ?>><?php echo esc_html( _x( 'Sidebar One', 'advertisement position name', 'awpcp-campagin-manager' ) ); ?></option>
        <option value="sidebar-two"<?php echo $instance['position'] == 'sidebar-two' ? ' selected="selected"' : ''; ?>><?php echo esc_html( _x( 'Sidebar Two', 'advertisement position name', 'awpcp-campagin-manager' ) ); ?></option>
        <option value="footer"<?php echo $instance['position'] == 'footer' ? ' selected="selected"' : ''; ?>><?php echo esc_html( _x( 'Footer', 'advertisement position name', 'awpcp-campagin-manager' ) ); ?></option>
    </select>
</p>
