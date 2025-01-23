# Strip Database Dump

This is a tiny plugin, which adds shorthands to WP-CLI which allows you to easily create database dumps and exclude sensitive data. It supports WooCommerce and Easy Digital Downloads.

It works as follows:

- `--strip-users`: excludes the `wp_users` and `wp_usermeta` tables.
- `--strip-customers`: excludes Customer related tables for WooCommerce and Easy Digital Downloads.
- `--strip-orders`: excludes Order related tables for WooCommerce and Easy Digital Downloads.

## Example

To create a database that excludes all of the above and save it one directory up from the current directory, run it as follows:

`wp strip_db dump ../custom.sql --strip-users --strip-customers --strip-orders`

If no filename is provided, a random one will be generated and saved in WordPress' root directory.
