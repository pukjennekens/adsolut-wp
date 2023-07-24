<div class="wrap">
    <?php if( isset( $_GET['settings-updated'] ) ): ?>
        <?php add_settings_error( 'adsolut_messages', 'adsolut_message', __( 'Instellingen opgeslagen', 'adsolut' ), 'updated' ); ?>
    <?php endif; ?>

    <?php settings_errors( 'adsolut_messages' ); ?>

    <h1><?php _e( 'Adsolut koppeling instellingen', 'adsolut' ); ?></h1>

    <form action="options.php" method="post">
        <?php settings_fields( 'adsolut' ); ?>
        <?php do_settings_sections( 'adsolut' ); ?>

        <div class="form-buttons-row">
            <?php submit_button( __( 'Opslaan', 'adsolut' ) ); ?>

            <?php if( $is_configured ): ?>
                <?php if( ! $is_logged_in ): ?>
                    <a class="button button-secondary" href="<?php echo esc_url( $auth_url ); ?>"><?php _e( 'Inloggen met Adsolut', 'adsolut' ); ?></a>
                <?php else: ?>
                    <a class="button button-secondary" href="<?php echo esc_url( $logout_uri ); ?>"><?php _e( 'Log uit', 'adsolut' ); ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </form>

    <?php if( $is_configured ): ?>
        <div class="form-buttons-row">
            <form action="<?= admin_url( 'admin-post.php' ); ?>" method="post">
                <input type="hidden" name="action" value="adsolut_test_mode">

                <?php if( $test_mode ): ?>
                    <input type="submit" class="button button-secondary button-test-mode" value="<?php _e( 'Test/development mode uit', 'adsolut' ); ?>">
                <?php else: ?>
                    <input type="submit" class="button button-secondary button-test-mode" value="<?php _e( 'Test/development mode aan', 'adsolut' ); ?>">
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>

    <hr>

    <h2><?php _e( 'Status', 'adsolut' ); ?></h2>

    <p><?php _e( 'Bekijk hier hoe de koppeling ervoor staat, en of er eventuele fouten zijn.', 'adsolut' ); ?></p>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><?php _e( 'Status', 'adsolut' ); ?></th>
                <td>
                    <?php if( $is_configured ): ?>
                        <span class="dashicons dashicons-yes"></span>
                        <span class="description"><?php _e( 'De plugin is geconfigureerd', 'adsolut' ); ?></span>
                    <?php else: ?>
                        <span class="dashicons dashicons-no"></span>
                        <span class="description"><?php _e( 'De plugin is niet geconfigureerd', 'adsolut' ); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( '(Fout)meldingen', 'adsolut' ); ?></th>
                <td class="adsolut-plugin-notifications">
                    <?php if( empty( $errors ) ): ?>
                        <span class="dashicons dashicons-yes"></span>
                        <span class="description"><?php _e( 'Er zijn geen foutmeldingen', 'adsolut' ); ?></span>
                    <?php else: ?>
                        <h4 class="description">
                            <span class="dashicons dashicons-no"></span>
                            <?php _e( 'Er zijn (fout)meldingen', 'adsolut' ); ?>
                        </h4>

                        <ul>
                            <?php foreach( $errors as $error ): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( 'Ingelogd', 'adsolut' ); ?></th>
                <td>
                    <?php if( $is_logged_in ): ?>
                        <span class="dashicons dashicons-yes"></span>
                        <span class="description"><?php _e( 'Je bent ingelogd', 'adsolut' ); ?></span>
                    <?php else: ?>
                        <span class="dashicons dashicons-no"></span>
                        <span class="description"><?php _e( 'Je bent niet ingelogd', 'adsolut' ); ?></span>
                    <?php endif; ?>
                </td>
            </tr>

            <?php // if( $test_mode ): ?>
            <tr>
                <th scope="row"><?php _e( 'Tokens', 'adsolut' ); ?></th>
                <td id="tokens-debug">
                    <strong><?php _e( 'Access token', 'adsolut' ); ?></strong>
                    <br>
                    <code><?= $access_token; ?></code>
                    <br>
                    <strong><?php _e( 'Refresh token', 'adsolut' ); ?></strong>
                    <br>
                    <code><?= $refresh_token; ?></code>
                    <br>
                    <strong><?php _e( 'Expires at', 'adsolut' ); ?></strong>
                    <br>
                    <code><?= $expires_at; ?> / <?= wp_date( 'd-m-Y H:i:s', $expires_at ); ?></code>
                    <br>
                    <strong><?php _e( 'Auth code', 'adsolut' ); ?></strong>
                    <br>
                    <code><?= $authorization_code; ?></code>
                </td>
            </tr>
            <?php // endif; ?>
        </tbody>
    </table>

    <hr>

    <h2><?php _e( 'URL\'s', 'adsolut' ); ?></h2>

    <p><?php _e( 'Deze URL\'s zijn nodig om de Adsolut plugin te laten werken.', 'adsolut' ); ?></p>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><?php _e( 'Redirect URI', 'adsolut' ); ?></th>
                <td>
                    <input type="text" class="regular-text" value="<?php echo esc_attr( $redirect_uri ); ?>" readonly />
                    <p class="description"><?php _e( 'Dit is de redirect URL', 'adsolut' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( 'Logout URL', 'adsolut' ); ?></th>
                <td>
                    <input type="text" class="regular-text" value="<?php echo esc_attr( $logout_uri ); ?>" readonly />
                    <p class="description"><?php _e( 'Dit is de logout URL', 'adsolut' ); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <style>
        p.submit {
            margin: 0;
            padding: 0;
        }

        .form-buttons-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .dashicons-yes {
            color: #46b450;
        }

        .dashicons-no {
            color: #dc3232;
        }

        .adsolut-plugin-notifications h4 {
            margin-top: 0;
        }

        .adsolut-plugin-notifications ul {
            list-style: disc;
            padding-left: 1rem;
        }

        .button.button-secondary.button-test-mode {
            color: #faaf4d;
            border-color: #faaf4d;
        }

        #tokens-debug {
            overflow-wrap: break-word;
            max-width: 400px;
        }

        #tokens-debug code {
            display: block;
            margin: 0.5rem 0;
            padding: 0.5rem;
            background-color: #fff;
            border-radius: 4px;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            $('input[type="hidden"]').each(function() {
                if( $(this).attr('name').startsWith('adsolut_settings') ) {
                    $(this).parent().parent().hide();
                }
            });
        });
    </script>
</div>