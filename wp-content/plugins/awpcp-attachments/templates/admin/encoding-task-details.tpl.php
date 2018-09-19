<div class="encoding-task">

    <h3><?php echo esc_html( __( 'Task Properties', 'awpcp-attachments' ) ); ?></h3>

    <dl class="encoding-task-properties clearfix">
        <dt class="encoding-task-property-name"><?php echo esc_html( __( 'Task', 'awpcp-attachments' ) ); ?></dt>
        <dd class="encoding-task-property-value"><?php echo $task->get_id(); ?></dd>
        <dt class="encoding-task-property-name"><?php echo esc_html( __( 'Source Video', 'awpcp-attachments' ) ); ?></dt>
        <dd class="encoding-task-property-value"><?php echo esc_html( $task->get_metadata( 'source_file' ) ); ?></dd>
        <dt class="encoding-task-property-name"><?php echo esc_html( __( 'Target Video', 'awpcp-attachments' ) ); ?></dt>
        <dd class="encoding-task-property-value"><?php echo esc_html( $task->get_metadata( 'target_file' ) ); ?></dd>
        <dt class="encoding-task-property-name"><?php echo esc_html( __( 'Format', 'awpcp-attachments' ) ); ?></dt>
        <dd class="encoding-task-property-value"><?php echo esc_html( $format['name'] ); ?></dd>
        <dt class="encoding-task-property-name"><?php echo esc_html( __( 'Command', 'awpcp-attachments' ) ); ?></dt>
        <dd class="encoding-task-property-value"><?php echo esc_html( $process['command'] ); ?></dd>
        <dt class="encoding-task-property-name"><?php echo esc_html( __( 'Log File', 'awpcp-attachments' ) ); ?></dt>
        <dd class="encoding-task-property-value"><?php echo esc_html( $process['log_file'] ); ?></dd>
        <dt class="encoding-task-property-name"><?php echo esc_html( __( 'PID', 'awpcp-attachments' ) ); ?></dt>
        <dd class="encoding-task-property-value"><?php echo esc_html( $process['pid'] ); ?></dd>
        <dt class="encoding-task-property-name"><?php echo esc_html( __( 'Status', 'awpcp-attachments' ) ); ?></dt>
        <dd class="encoding-task-property-value"><?php echo esc_html( $task->format_status() ); ?></dd>
    </dl>

    <h3><?php echo esc_html( __( 'Task Log', 'awpcp-attachments' ) ); ?></h3>

    <pre class="encoding-task-log"><code><?php echo esc_html( file_get_contents( $process['log_file'] ) ); ?></code></pre>
</div>
