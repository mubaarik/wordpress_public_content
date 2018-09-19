<?php

function awpcp_extra_fields_column_manager() {
    return new AWPCP_Extra_Fields_Column_Manager(
        $GLOBALS['wpdb'],
        awpcp_database_helper()
    );
}


class AWPCP_Extra_Fields_Column_Manager {

    private $db;
    private $database_helper;

    public function __construct( $db, $database_helper ) {
        $this->db = $db;
        $this->database_helper = $database_helper;
    }

    public function create_field_column_if_necessary( $field ) {
        if ( ! awpcp_column_exists( AWPCP_TABLE_ADS, $field->field_name ) ) {
            $this->create_field_column( $field->field_name, $field->field_mysql_data_type );
        }
    }

    public function create_field_column( $column_name, $column_type ) {
        $sql = 'ALTER TABLE ' . AWPCP_TABLE_ADS . ' ADD `%s` %s';
        $sql = sprintf( $sql, $column_name, $this->get_column_description( $column_type ) );

        $result = $this->db->query( $sql );

        if ( $result === false ) {
            $message = __( 'There was an error trying to create the column <column-name> in the Ads table.', 'awpcp-extra-fields' );
            $message = str_replace( '<column-name>', '<strong>' . $column_name . '</strong>', $message );

            throw new AWPCP_Exception( $message );
        }
    }

    private function get_column_description( $column_type ) {
        if ($column_type == 'INT') {
            $description = 'INT(10) DEFAULT NULL';
        } elseif ($column_type == 'FLOAT') {
            $description = 'FLOAT(12,2) DEFAULT NULL';
        } elseif ($column_type == 'VARCHAR') {
            $description = $this->database_helper->replace_charset_and_collate( 'VARCHAR(500) COLLATE <collate>' );
        } elseif($column_type == 'TEXT') {
            $description = $this->database_helper->replace_charset_and_collate( 'TEXT COLLATE <collate>' );
        }

        return $description;
    }

    public function update_field_column_if_necessary( $field, $new_column_name, $new_column_type ) {
        if ( $field->field_name != $new_column_name || $field->field_mysql_data_type !== $new_column_type ) {
            $this->update_field_column( $field->field_name, $new_column_name, $new_column_type );
        }
    }

    private function update_field_column( $old_column_name, $new_column_name, $new_column_type ) {
        $sql = 'ALTER TABLE ' . AWPCP_TABLE_ADS . ' CHANGE `%s` `%s` %s';
        $sql = sprintf( $sql, $old_column_name, $new_column_name, $this->get_column_description( $new_column_type ) );

        $result = $this->db->query( $sql );

        if ( $result === false ) {
            $message = __( 'There was an error trying to update the column <column-name> in the Ads table.', 'awpcp-extra-fields' );
            $message = str_replace( '<column-name>', '<strong>' . $column_name . '</strong>', $message );

            throw new AWPCP_Exception( $message );
        }
    }
}
