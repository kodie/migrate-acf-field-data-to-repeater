# Migrate ACF Field Data to Repeater WP CLI Command

**Warning: This plugin and command should be considered experiemental. Use at your own risk. Be sure to test thoroughly before using in a production environment.**

A WordPress plugin that adds the `wp migrate-acf-field-data-to-repeater` command to WP CLI. That command renames the keys of metadata so that they are prepended with the repeater slug so that the fields can be moved into a repeater along with their data.

For example: I have a field created with ACF named `item_description` and I have been using it on pages so that field has content in it on multiple pages. Later on I decided that I want a repeater field named `items` and I want `item_description` to be in that repeater. Well, I can easily drag the field into the repeater and there we go! Except, not really. The previously entered data for `item_description` will not appear in the new repeater field. That's where this command comes into play.

Use this command after you have already created your new repeater field and moved the fields you with to migrate into the repeater from the Custom Fields page.

Easily install and activate the plugin via:

```sh
$ composer install kodie/migrate-acf-field-data-to-repeater
$ wp plugin activate migrate-acf-field-data-to-repeater
```


## `wp migrate-acf-field-data-to-repeater <repeater_slug> <repeater_field_id> <field_slug...> [--dry-run] [--post_type=POST_TYPE] [--include=POST_ID,POST_ID,POST_ID...]`

 - `repeater_slug` - The "name" field for the repeater. Example: `items`
 - `repeater_field_id` - This is the field ID for the repeater. To find this, you'll need to go to a page on the WordPress backend that has the field, inspect the element, and it should be in the `for` attribute for the repeater label prepended with "field_". Example: `636d5921b3451`
 - `field_slug` - The "name" field for the field(s) you wish to move into the repeater. Seperate multiple slugs with a comma. Example: `item_title,item_description`
 - `--dry-run` - Set this option to display the results without actually making any changes.
 - `--post_type` and `--include` - You can actually use any options to pass to the [get_posts](https://developer.wordpress.org/reference/functions/get_posts) function. Example: `--post_type=page --include=47,82 --author_name=bob`


## Thanks

This plugin was originally inspired by [this gist](https://gist.github.com/gthayer/0d71df7cb325549cc37661f1c9378fd9) by [@gthayer](https://github.com/gthayer).


## License

MIT. See the [license.md file](license.md) for more info.