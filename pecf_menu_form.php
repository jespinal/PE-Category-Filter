<div class="wrap">
    <form method="post" action="options.php">
<?php settings_fields( 'pecf_option-group' ); ?>
<?php do_settings_sections('pecf_categ-menu'); ?>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
</div>