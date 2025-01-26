# WP-CLI Strip Database Dump

This is a tiny plugin, which adds shorthands to WP-CLI which allows you to easily create database dumps and exclude
sensitive data.

The following options are available:

- `--users`: excludes the `wp_users` and `wp_usermeta` tables.
- `--customers`: excludes Customer related tables for supported 3rd party plugins.
- `--orders`: excludes Order related tables for supported 3rd party plugins.
- `--all`: exclude all of the above.

After running the command, 2 separate database exports are created:

1. `[your-filename]-1.sql` containing all tables where data should be retained.
2. `[your-filename]-2.sql` containing all tables where all data should be stripped.

> [!IMPORTANT]
> The created DB dumps must be imported in the provided order.

### Usage

To create a database that excludes orders, customers and users and save it one directory up from the current directory,
run it as
follows:

````
wp strip-db dump ../stripped-db-dump.sql --all
````

To just strip customer data and store the dump in the current directory, run:

````
wp strip-db dump stripped-dump --customers
````

> [!NOTE]
> It's not required to append a file extension to the filename argument.

If no filename is provided, a random one will be generated and saved in WordPress' root directory.

> [!IMPORTANT]
> When the `--users` parameter is added to the command, the `users` table will be empty when importing it to your
> database. This means you need to create at least a new administrator user after importing the generated table. This
> can
> be done using the following
> WP-CLI command: `wp user create username email@address.com --role=administrator`.

## 3rd Party Plugin Support

The plugin currently removes sensitive data (i.e. any data containing personal information) for the following plugins:

* AffiliateWP
* Easy Digital Downloads
* WooCommerce
* WPForms

> [!TIP]
> If You want this plugin to support more 3rd party plugins, feel free to submit a pull request!

As of v1.1.1 adding support for additional 3rd party plugins is easy. It's a matter of adding a class to
`src/Compatibility` and implementing this plugin's `CompatibilityInterface` along with the required methods. Each method
should return an array of corresponding table names **without prefix!** Then, add the new class to the corresponding
handlers in `Main` and you're ready to submit your PR! :-)

## Installation

[Download the latest release](https://github.com/Dan0sz/strip-db-dump/releases/latest/download/daan-strip-db-dump.zip)
from the Releases page (or click the link) and install it in WordPress:

1. Navigate to your Administrator area,
2. Go to Plugins >> Add New Plugin
3. Click the Upload Plugin button in the top-left of the screen
4. **Browse...** to the ZIP file and install it by clicking **Install Now**.
