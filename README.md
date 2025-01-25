# WP-CLI Strip Database Dump

This is a tiny plugin, which adds shorthands to WP-CLI which allows you to easily create database dumps and exclude
sensitive data. It supports AffiliateWP, Easy Digital Downloads, WooCommerce and WPForms.

> [!TIP]
> If You want this plugin to support more 3rd party plugins, feel free to submit a pull request!

The following options are available:

- `--users`: excludes the `wp_users` and `wp_usermeta` tables.
- `--customers`: excludes Customer related tables for WooCommerce and Easy Digital Downloads.
- `--orders`: excludes Order related tables for WooCommerce and Easy Digital Downloads.

After running the command, 2 separate database exports are created:

1. `[your-filename]-1.sql` containing all tables where data should be retained.
2. `[your-filename]-2.sql` containing all tables where all data should be stripped.

> [!IMPORTANT]
> The created DB dumps must be imported in the provided order.

### Usage

To create a database that excludes all of the above and save it one directory up from the current directory, run it as
follows:

````
wp strip-db dump [filename] --users --customers --orders
````

If no filename is provided, a random one will be generated and saved in WordPress' root directory.

> [!IMPORTANT]
> When the `--users` parameter is added to the command, the `users` table will be empty when importing it to your
> database. This means you need to create at least a new administrator user after importing the generated table. This
> can
> be done using the following
> WP-CLI command: `wp user create username email@address.com --role=administrator`.

## Installation

[Download the latest release](https://github.com/Dan0sz/strip-db-dump/releases/latest/download/daan-strip-db-dump.zip)
from the Releases page (or click the link) and install it in WordPress:

1. Navigate to your Administrator area,
2. Go to Plugins >> Add New Plugin
3. Click the Upload Plugin button in the top-left of the screen
4. **Browse...** to the ZIP file and install it by clicking **Install Now**.
