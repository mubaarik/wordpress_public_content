<?php

function awpcp_repair_extra_fields_columns_admin_page() {
    return new AWPCP_Repair_Extra_Fields_Columns_Admin_Page();
}

/**
 * @since 3.5.9
 */
class AWPCP_Repair_Extra_Fields_Columns_Admin_Page extends AWPCP_AdminPage {

    public function __construct() {
        parent::__construct(
            'awpcp-repair-extra-fields-columns-admin-page',
            __( 'Repair Extra Fields Columns Admin Page', 'awpcp-extra-fields' ),
            null
        );
    }

    public function dispatch() {
        wp_enqueue_script( 'awpcp-admin-manual-upgrade' );

        $tasks_definitions = array(
            array(
                'name' => __( 'Repair Extra Fields Columns Admin Page', 'awpcp-extra-fields' ),
                'action' => 'awpcp-repair-extra-fields-columns-admin-page',
            ),
        );

        $manage_extra_fields_url = add_query_arg( 'page', 'Configure5', admin_url( 'admin.php' ) );
        $success_message = __('Congratulations. We finished verifying and repairing your extra fields. Go back to <a>Manage Extra Fields</a> section.', 'awpcp-extra-fields' );
        $success_message = str_replace( '<a>', '<a href="' . $manage_extra_fields_url . '">', $success_message );

        $messages = array(
            'introduction' => __( 'Click the button below to verify that all extra fields have a corresponding column in the ads table. A new column will be added for fields without a proper one.', 'awpcp-extra-fields' ),
            'success' => $success_message,
            'button' => __( 'Repair Extra Fields', 'awpcp-extra-fields' ),
        );

        $tasks = new AWPCP_AsynchronousTasksComponent( $tasks_definitions, $messages );

        echo $this->render( 'content', $tasks->render() );
    }
}
