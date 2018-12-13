=== BCE User List ===
Contributors: d1m1
Tags: coding test, users
Requires at least: 4.9.8
Tested up to: 4.9.8
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a user list plugin built as a coding exercise for Blue Coding.

== Description ==
This is a plugin built as a coding exercise for Blue Coding. The tasks to be performed are:

* Create a plugin that lists WordPress users (users registered in wp_users table)
* In the user list page, show name, email and user type (Administrator, Editor, etc.), and put two buttons, one to update user info, and another to deactivate the user.
* In the user update page, only allow the Name to be updated.
* The plugin must have a \'readme.txt\' file that includes step-by-step installation. Assume no programming knowledge.

Bonus points:
1. Add dynamic dropdown filter by user type.
2. Bulk deactivate multiple users.

== Installation ==
If you are reading this on the plugin\'s GitHub page:

1. Click on \"Clone or download\", then click on \"Download .zip\"
2. Continue on steps a. or b. below, depending on how you want to install this plugin.

If you already have the plugin\'s .zip file:

a. Manual plugin installation through the WordPress dashboard

1. Login to your dashboard by going to yourdomain.com/wp-login.php (replace \"yourdomain.com\" with your actual domain name)
2. On the menu to the left, find and click on Plugins > Add new
3. Near the top of the page, right below the \'Add Plugin\' title, click on \'Upload Plugin\'. A file selection box will appear below.
4. Click on \'Select File\', browse to where you downloaded the \'bce-user-list.zip\' file and select it.
5. Click on \'Install now\'.
6. Continue on step 2 of \'After either installation method\', below.

b. FTP upload instructions:

1. Most operating systems (Windows, Mac, and so on) have built-in tools to open Zip files. Unpack (extract/unzip) the \"bce-user-list.zip\" on a location of your choice (e.g.: \'Desktop\', or other folder that is easily accesible). You should get a \"bce-user-list\" folder.
2. Connect to your site’s server using FTP. If you have any difficulty connecting to your server, contact your hosting provider and ask for assistance in connecting to your server via FTP.
3. Find your WordPress site installation folder and navigate to it.
4. Upload the entire \"bce-user-list\" folder to the \"/wp-content/plugins/\" directory via FTP.

After either installation method:

1. Login to your dashboard by going to yourdomain.com/wp-login.php (replace \"yourdomain.com\" with your actual domain name)
2. On the menu to the left, find and click on Plugins > Installed Plugins.
3. Find and activate the \"BCE User List\" plugin through the \"Plugins\" menu in WordPress.
4. Done.

The plugin can be found under the User menu in the WordPress dashboard.

== Details ==

This plugin aims to use best practices such as the ones on the [WordPress Plugin Developer Handbook](https://developer.wordpress.org/plugins/the-basics/best-practices/). As such, a [Boilerplate Code Generator](https://wppb.me/) was used for the initial version of the files, and then were edited accordingly.

Thought process on developing this plugin:

First, we need entry points to the plugin itself through the WordPress dashboard, so a \"BCE User List\" menu item was added under Users.

In order for keep the look and feel of the plugin the same or close to the WordPress tables, I extended the class wp-users-list-table under wp-admin, making sure to namespace it to avoid collisions.

Then, it was just a matter of adding the required functionality, such as listing the users\' details and handling deactivation or editing details.