<?php
/*
Plugin Name: Migrate ACF Field Data to Repeater WP CLI Command
Version: 0.0.1
Description: Does what the title says. Adds the `wp migrate-acf-field-data-to-repeater` command to WP CLI. See readme.md for info.
Author: Kodie Grantham
Author URI: https://kodieg.com
Plugin URI: https://github.com/kodie/migrate-acf-field-data-to-repeater
*/

if (!defined('ABSPATH')) die('No direct access allowed');

if (!class_exists('Migrate_ACF_Field_Data_To_Repeater')) {
  class Migrate_ACF_Field_Data_To_Repeater {
    function __construct() {
      if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::add_command('migrate-acf-field-data-to-repeater', array(&$this, 'cmd'), array(
          'shortdesc' => 'Migrates data for a field that has been moved into an ACF repeater field.'
        ));
      }
    }

    function cmd($args, $assoc_args) {
      $repeater_slug = array_shift($args);
      $repeater_field_id = array_shift($args);
      $field_slugs = $args;
      $dry_run = isset($assoc_args['dry-run']) && $assoc_args['dry-run'];

      unset($assoc_args['dry-run']);

      $assoc_args = $this->parse_cmd_args($assoc_args);
      $results = $this->move_fields_data_into_repeater($repeater_slug, $repeater_field_id, $field_slugs, $assoc_args, $dry_run);

      WP_CLI::success("Changed {$results['updated_fields']} fields across {$results['updated_posts']} posts.");

      if ($dry_run) {
        WP_CLI::log('Not really, that was just a dry run.');
      }
    }

    function move_fields_data_into_repeater($repeater_slug, $repeater_field_id, $field_slugs, $query_args, $dry_run = false) {
      $query_args = array_merge(array(
        'posts_per_page' => -1
      ), $query_args);

      $posts = get_posts($query_args);
      $updated_posts = 0;
      $updated_fields = 0;

      foreach ($posts as $post) {
        $meta = get_post_meta($post->ID);
        $updated_post = false;
        $repeater_keys = array();
    
        foreach ($meta as $meta_key => $meta_value) {
          $updated_field = false;

          foreach ($field_slugs as $field_slug) {
            if (strpos($meta_key, $field_slug) !== false && strpos($meta_key, $repeater_slug) === false) {
              $new_field_slug = $repeater_slug . '_0_' . $field_slug;
              $new_meta_key = str_replace($field_slug, $new_field_slug, $meta_key);
              $repeater_key = substr($new_meta_key, 0, strpos($new_meta_key, $repeater_slug) + strlen($repeater_slug));

              if (!in_array($repeater_key, $repeater_keys)) $repeater_keys[] = $repeater_key;

              if (defined('WP_CLI') && WP_CLI) {
                WP_CLI::log("Moving \"$meta_key\" to \"$new_meta_key\" for post ID {$post->ID}...");
              }

              if (!$dry_run) {
                foreach($meta_value as $value) {
                  add_post_meta($post->ID, $new_meta_key, maybe_unserialize($value));
                }

                delete_post_meta($post->ID, $meta_key);
              }
    
              $updated_field = true;
              break;
            }
          }

          if ($updated_field) {
            $updated_post = true;
            $updated_fields++;
          }
        }

        if ($updated_post) {
          foreach($repeater_keys as $repeater_key) {
            $repeater_key_value = substr($repeater_key, 0, 1) === '_' ? 'field_' . $repeater_field_id : 1;

            if (defined('WP_CLI') && WP_CLI) {
              WP_CLI::log("Creating \"$repeater_key\" with value \"$repeater_key_value\" for post ID {$post->ID}...");
            }

            if (!$dry_run) {
              update_post_meta($post->ID, $repeater_key, $repeater_key_value);
            }
          }

          $updated_posts++;
        }
      }

      return compact('updated_fields', 'updated_posts');
    }

    function parse_cmd_args($args) {
      foreach($args as $query_key => $query_key_value) {
        if (is_numeric($query_key_value)) {
          $args[$query_key] = intval($query_key_value);
        } elseif (strpos($query_key_value, ',') !== false) {
          $args[$query_key] = $this->parse_cmd_args(explode(',', $query_key_value));
        }
      }

      return $args;
    }
  }

  function migrate_acf_field_data_to_repeater() {
    global $migrate_acf_field_data_to_repeater_instance;
    if (!isset($migrate_acf_field_data_to_repeater_instance)) $migrate_acf_field_data_to_repeater_instance = new Migrate_ACF_Field_Data_To_Repeater();
    return $migrate_acf_field_data_to_repeater_instance;
  }

  migrate_acf_field_data_to_repeater();
}
?>