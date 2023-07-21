<div class="wrap">
    <?php if( isset( $_GET['settings-updated'] ) ): ?>
        <?php add_settings_error( 'adsolut_messages', 'adsolut_message', __( 'Instellingen opgeslagen', 'adsolut' ), 'updated' ); ?>
    <?php endif; ?>

    <?php settings_errors( 'adsolut_messages' ); ?>

    <h1><?php _e( 'Adsolut koppeling instellingen', 'adsolut' ); ?></h1>

    <form action="options.php" method="post">
        <?php settings_fields( 'adsolut_general_settings' ); ?>
        <?php do_settings_sections( 'adsolut_general_settings' ); ?>
        <?php submit_button( __( 'Opslaan', 'adsolut_general_settings' ) ); ?>
    </form>
</div>