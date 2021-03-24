=== Media File Renamer - Auto & Manual Rename ===
Contributors: TigrouMeow
Tags: rename, file, media, move, seo, files, renamer, optimize, library
Donate link: https://commerce.coinbase.com/checkout/d047546a-77a8-41c8-9ea9-4a950f61832f
Requires at least: 5.0
Tested up to: 5.7
Requires PHP: 5.6
Stable tag: 5.1.8

Renames your media files for better SEO and a nicer filesystem (automatically or manually).

== Description ==
Renames your media files for better SEO and a nicer filesystem (automatically or manually). For more information, please visit the official website: [Media File Renamer](https://meowapps.com/plugin/media-file-renamer/).

=== HOW IT WORKS ===
Media File Renamer, by default, automatically renames the filenames of your Media entries based on their titles. You can trigger this, or you can let it happen every time you modify titles. You can also rename the files manually. The references to those files will be also updated (posts, pages, custom types, metadata, etc...). You can use the Media Library, or the Media Edit screen.

However, it is highly recommended to use the pretty and very dynamic Renamer Dashboard. If you like to work fast and well, you will really love working with this modern dashboard.

[youtube https://youtu.be/XPbKE8pq0i0]

Please have a look at the [tutorial](https://meowapps.com/media-file-renamer-tutorial/).

=== COMPATIBILITY ===
It works with a lot of features of WordPress and other plugins, such as Retina files, WebP, rescaled image (since WP 5.3), PDF Thumbnails, UTF8 files, optimized images, various encodings, etc. There are too many handled and specific cases to be listed here, but we are doing our best to keep up with everything :)

=== PRO VERSION ===
More features are added in the [Pro Version](https://meowapps.com/plugin/media-file-renamer/), such as:
- Transliteration (replace various accents, emoticons, umlauts, cyrillic, diacritics, by their ASCII equivalent)
- Automatic renaming based on the attached posts, products (and other post types), or ALT text
- Anonymizer (rename the files with anonymous files)
- Move files to another directory
- Metadata syncing (ALT text, title, etc)
- Numbered files (to allow similar filenames to be renamed)
- Force Rename (if your install is broken, this will help you to re-link your media entries to your files)

=== BE CAREFUL: PREPARE A BACKUP ===
Renaming (or moving) files is a dangerous process. Before doing anything in bulk, try renaming your files on by one, then check if the references (in your pages) have been updated properly. The renaming can't cover all use cases, as some plugins are unfortunately using unconventional ways to encode the usage of the files. Therefore, **it is absolutely necessary to backup your files and database** in order to enjoy this plugin at its full extent. 

=== WHEN SOMETHING BAD HAPPENS ===
If your website seems broken after a few renames, try to **clear your cache**. The cached HTML is often using the old references. You can also enable the Undo feature and try to rollback to the previous filenames. If references aren't updated properly, please write a nice post (not an angry one) in the support threads :) I am trying my best to cover more and more use cases. Please have a look here: [Questions & Issues](https://meowapps.com/media-file-renamer-faq-issues/).

=== A SIMPLER PLUGIN ===
If you only need an editable field in order to modify the filename, please try [Phoenix Media Rename](https://wordpress.org/plugins/phoenix-media-rename). It's simpler, and just does that. And yes, we are friends and we collaborate! :)

=== FOR DEVELOPERS ===
The plugin can be tweaked in many ways, there are many actions and filters available. Through them, for example, you can customize the automatic renaming to your liking. There is also a little API that you can call. More about this [here](https://meowapps.com/media-file-renamer-faq/).

== Installation ==

1. Upload the plugin to your WordPress.
2. Activate the plugin through the 'Plugins' menu.
3. Try it with one file first! :)

== Upgrade Notice ==

1. Replace the plugin with the new one.
2. Nothing else is required! :)

== Screenshots ==

1. Type in the name of your media, that is all.
2. Special screen for bulk actions.
3. This needs to be renamed.
4. The little lock and unlock icons.
5. Options for the automatic renaming (there are more options than just this).

== Changelog ==

= 5.1.8 (2021/03/04) =
* Add: Search.
* Add: Quick rename the title from the dashboard.

= 5.1.7 (2021/02/21) =
* Fix: The Synchronize Media Title option wasn't working logically.

= 5.1.6 (2021/02/12) =
* Fix: References for moved files were not updated.
* Add: Sanitize filename after they have been through the mfrh_new_filename filter.

= 5.1.3 =
* Add: Greek support.
* Fix: Better sensitive file check.
* Fix: Manual rename with WP CLI.

= 5.1.2 =
* Add: Auto attach feature.
* Add: Added Locked in the filters.
* Update: Icons position.

= 5.1.1 =
* Fix: Issue with roles overriding and WP-CLI.
* Fix: Issue with REST in the Common Dashboard.

= 5.1.0 =
* Add: Support overriding roles.
* Fix: The layout of the dashboard was broken by WPBakery.

= 5.0.9 =
* Fix: Rules for renaming files with slashes were imperfect.
* Fix: ArgumentCountError with action_sync_alt (too few arguments).
* Update: Avoid useless async refreshes.

= 5.0.8 (2020/09/26) =
* Fix: Two options were not working very logically.
* Fix: Avoid errors when the PHP Error Logs is too big.
* Update: Use Nonce for Rest API.

= 5.0.7 (2020/09/08) =
* Fix: Incompatibility with WordPress 4.8.

= 5.0.6 (2020/09/08) =
* Update: A check for the rename status was performed for every entry in the Media Library all at the same time, and that was causing slower performance (especially when more than 10 or 20 entries are displayed per page). It's now performed with a maximum of two concurrent requests at a time.
* Note: If you like it, please review the plugin [by clicking here](https://wordpress.org/support/plugin/media-file-renamer/reviews/?rate=5#new-post). It's important for us :) Thank you!

= 5.0.5 (2020/09/04) =
* Fix: Issue with case sensitive filenames fixed.
* Update: Additional rules for auto-renaming.

= 5.0.4 (2020/08/29) =
* Fix: Undo All wasn't working on WordPress install with modified DB prefixes.
* Fix: Works with Plain Permalinks.
* Update: More natural feel for the field in the Media Library.

= 5.0.3 (2020/08/21) =
* Fix: Use MutationObserver to make sure the Renamer Field attach itself to dynamic Media Librairies.
* Fix: Fixed a notice related to WP 5.5.
* Update: Better handling of emoticons.

= 5.0.2 =
* Info: Brand new UI! Made everything much clearer.
* Fix: Method was not always taken in account.

= 5.0.1 =
* Info: Brand new UI! Made everything much clearer.
* Fix: Method was not always taken in account.
* Update: Accessibility and usability.
* Fix: Field wasn't displayed if no auto mode was selected.

= 5.0.0 =
* Info: Brand new UI! Made everything much clearer.
* Fix: Issues related to WebP and PDF thumbnails.
* Fix: Issues related to breaking characters in the Media or Attached Post Title.

= 4.7.0 =
* Fix: Issue with rare characters used in the title.
* Fix: Issue with PDF thumbnails.

= 4.6.9 =
* Add: There was an issue with Auto-Rename sometimes not appearing when the Sensitive Files Check was disabled.

= 4.6.8 =
* Add: New filter 'mfrh_allow_rename'. Developers can now allow/reject the renaming (useful for bulk).
* Fix: The file numbering wasn't working fine in a few specific cases.

= 4.6.7 =
* Add: Better handling of dots and hyphens (especially the non-standard ones).
* Add: Support for WebP.

= 4.6.5 =
* Fix: Little (i18n) fixes in the admin.
* Add: Doesn't show the button to the Dashboard if Auto is disabled.
* Update: Admin refreshed to 2.4.

= 4.5.9 =
* Fix: Column wasn't displayed when manual enabled and automatic disabled.

= 4.5.8 =
* Fix: Fixed the AJAX/REST check.
* Update: Admin refresh.

= 4.5.5 =
* Update: Dashboard and Updater... updated.
* Fix: The WooCommerce add-on file was missing.
* Fix: Search was sometimes not working properly in the Media Library.
* Info: We are working on a bunch of updates concerning the usage of dots, hyphens and other characters, so expect the renaming rules to change a bit (for the best).

= 4.5.2 =
* Update: Code cleaning, Youtube video.
* Fix: Issue with updating the ALT field.

= 4.4.0 =
* Update: Compatibility with WP 5.0.
* Update: Compatibility with Real Media Library.

= 4.2.8 =
* Fix: Better support for Real Media Library.
* Update: Improved transliteration.

= 4.2.7 =
* Fix: Removed Updraft.
* Update: UTF-8 is handled by default, no need to have an option for it.
* Add: Option for transliteration (cyrillic, accents, umlauts).

= 4.2.2 =
* Add: Polylang compatibility.
* Update: UI enhancements and attempt to make the renaming faster.

= 4.2.1 =
* Add: All the actions in the Media Library are now asynchronous. No more page reload!
* Update: Many changes and little enhancements in the code, for speed, security and code-tidiness.

= 4.0.4 =
* Fix: Renaming using filters (work in progress).
* Fix: Insensitive-case match was giving the wrong file in some cases (webp for instance).

= 4.0.2 =
* Fix: PDF thumbnails support.
* Update: Code improvement, faster SQL queries.

= 4.0.1 =
* Fix: Issue with the tolowercase feature.
* Fix: Extension issue with mfrh_new_filename filter.
* Add: Filter to rewrite Alt Text.

= 4.0.0 =
* Update: Huge code cleaning and major refactorization. The core was also rewritten.
* Add: Compatibility with Beaver Builder.
* Fix: Avoid looking for too much perfection (which is dangerous) when using numbered files.
* Fix: Works fine now with image sizes in the meta which has the... same size.

= 3.7.2 =
* Update: Now uploading Media into Post rename the filename accordingly.

= 3.7.1 =
* Fix: Rename on Upload issue in a few cases.

= 3.7.0 =
* Update: Improved Rename on Upload.
* Fix: Annoying warning (but it was not causing any error).

= 3.6.9 =
* Update: Manual Rename allows a new extension.

= 3.6.8 =
* Add: Little API.

= 3.6.7 =
* Add: Bulk rename in the Media Library.

= 3.6.6 =
* Add: Table with the filenames before and after renaming + CSV Export (works with Redirection plugin).

= 3.6.4 =
* Add: Button "Undo All" to restore all the original filenames.
* Fix: Avoid the Numbered Files and Force Renamed options to be activated at the same time.

= 3.6.0 =
* Fix: Compatibility with WPML.
* Fix: There was a compatibility issue with retina.

= 3.5.8 =
* Add: New button "Lock All"
* Fix: Button "Unlock All and Rename" was not really unlocking everything.

= 3.5.6 =
* Fix: Sometimes numbered files were renamed something like abc-2-2.jpg.
* Update: Rename with lowercase to avoid issues.
* Add: Option for Pro, Media Title is synchronized with Title of Attached Post.

= 3.5.4 =
* Add: mfrh_replace_rules filter allows you to personalize the renaming at the character level.

= 3.5.2 =
* Fix: Update system fixed and code cleaning.

= 3.4.5 =
* Fix: Better handling of umlauts.
* Info: There will be an important warning showing up during this update. It is an important announcement.

= 3.2.7 =
* Fix: Slug was not getting renamed after recent WP update.
* Fix: Tiny fixed to avoid notices.
* Add: Support for WPML Media (thanks to David García froml WPML Team).

= 3.2.4 =
* Fix: Should work with more plugins/themes, WooCommerce for example. The updates aren't done only on the full URLs of the images in the DB now but also on the relative uploads path as well.
* Info: If you have some time, please review me nicely at https://wordpress.org/support/view/plugin-reviews/media-file-renamer?rate=5#postform. Thanks to you, I get a lot of motivation to make this plugin better :)

= 3.2.3 =
* Add: Option to rename depending on the ALT. Useful if you get interesting information in your ALT.
* Update: Sync ALT also works with Attached Post Title.
* Fix: Better handling of norwegian letters (will improve this kind of things over time).

= 3.2.2 =
* Add: Rename the file during upload, based on the media title. Not by default, check the options :)

= 3.2.0 =
* Fix: Logging could not be enabled.
* Update: Code cleaning.

= 3.1.0 =
* Update: The UI was a bit modified and enhanced. I also think it is simpler and cleaner.
* Update: Removed the auto-flagging process which was causing issues on sizeable installs.

= 3.0.0 =
* Fix: The references in the excerpts are now also updated (they are used by WooCommerce).
* Add: Undo button. When the media is unlocked and has been renamed, you have a Undo button. You need to active this in the option.
* Update: Everything has been moved into the Meow Apps menu for a cleaner admin.

= 2.7.8 =
* Fix: Removed Flattr.
* Add: Additional cleaning to avoid extensions sometimes written in the title by WP.
* Add: Clean out the english apostrophe 's during the creation of the new filename.

= 2.7.6 =
* Add: New option to remove the ad, the Flattr button and the information message about the Pro.
* Fix: Renaming slug was not working well after latest WordPress updates
* Fix: Use direct links for all my images and links to follow WordPress rules.

= 2.7.1 =
* Info: A file mfrh_custom.php has been added. If you are an advanced users or a developer, please have a look at the FAQ here: https://wordpress.org/plugins/media-file-renamer/faq/. Since I am only one developer, I can't cover all the renaming cases we have (since sometimes plugins keep their own links to the filenames; such as WooCommerce). That will make it easy to advanced users to push Media File Renamer to cover more and more special cases.

= 2.6.9 =
* Change: Modified description and information about the mfrh_url_renamed and mfrh_media_renamed filters.
* Add: New option to force renaming file (even though the file failed to be renamed). That will help PRO users to fix their broken install, often after a migration for example (often related to change of hosting service using different encoding).
* Fix: Click on lock/unlock doesn't take you back to the first page anymore.
* Fix: Little naming issue when numbering + custom filter is used.

= 2.6.0 =
* Add: Lock/Unlock icons in the Media Library.
* Add: Rename depending on the title of the post the media is attached to.

= 2.5.0 =
* Update: WordPress 4.4.
* Add: Add -2, -3, etc... when filenames are similar. Pro only.
* Fix: There was a glitch when .jpeg extension were used. Now keep them as .jpeg.

= 2.4.0 =
* Fix: There was a possibility that the image sizes filenames could be overwritten wrongly.
* Update: Rename the GUID (File Name) is now the default. Too many people think it is a bug while it is not.
* Add: UTF-8 support for renaming files. Before playing with this, give it a try. Windows-based hosting service will probably not work well with this.
* Fix: Auto-Rename was renaming files even though it was disabled.
* Update: If Auto-Rename is disabled, the Media Library column is not shown anymore, neither is the dashboard (they are useless in that case).
* Add: Metadata containing '%20' instead of spaces are now considered too during the renaming.

= 2.3.0 =
* Add: Update the metadata (true by default).
* Fix: Guid was renamed wrongly in one rare case.
* Fix: Double extension issue with manual renaming.

= 2.2.4 =
* Fix: Couldn't rename automatically the files without changing the titles, now the feature is back.
* Fix: Better 'explanations' before renaming.
* Fix: Should work with WPML Media now.
* Fix: Manage empty filenames by naming them 'empty'.

= 2.2.2 =
* Add: Option to automatically sync the alternative text with the title.
* Add: Filters and Actions to allow plugins (or custom code) to customize the renaming.
* Fix: Avoid to rename file if title is not changed (annoying if you previously manually updated it).
* Change: Plugin functions are only loaded if the user is using the admin.

= 2.2.0 =
* Add: Many new options.
* Add: Pro version.
* Add: Manual file rename.
* Update: Use actions for renaming (to facilitate support for more renaming features).

= 2.0.0 =
* Fix: Texts.
* Fix: Versioning.

= 1.9.4 =
* Add: New option to avoid to modify database (no updates, only renaming).
* Add: New option to force update the GUID (aka "File name"...). Not recommended _at all_.
* Fix: Options were without effect.
* Fix: GUID issue.

= 1.3.4 =
* Fix: issue with attachments without metadata.
* Fix: UTF-8 title name (i.e. Japanese or Chinese characters).

= 1.3.0 =
* Add: option to rename the files automatically when a post is published.

= 1.2.2 =
* Fix: the 'to be renamed' flag was not removed in a few cases.

= 1.2.0 =
* Fix: issue with strong-caching with WP header images.
* Fix: now ignore missing files.
* Change: renaming is now part of the Media Library with nice buttons.
* Change: the dashboard has been moved to Tools (users should use the Media Library mostly).
* Change: no bubble counter on the dashboard menu; to avoid plugin to consume any resources.

= 1.0.4 =
* Fix: '<?' to '<?php'.
* Add: French translation.
* Change: Donation button (can be removed, check the FAQ).

= 1.0.2 =
* Fix: Ignore 'Header Image' to avoid related issues.
* Change: Updated screenshots.
* Change: 'To be renamed' filter removed (useless feature).

= 1.0.0 =
* Change: Rename Dashboard enhanced.
* Change: Scanning function now displays the results nicely.
* Change: Handle the media with 'physical' issues.

= 0.9.4 =
* Fix: Works better on Windows (file case).
* Fix: doesn't add numbering when the file exists already - was way too dangerous.
* Change: warns you if the Media title exists.
* Fix: Removed a 'warning'.

= 0.9 =
* Fix: Media were not flagged "as to be renamed" when the title was changed during editing a post.
* Change: Internal optimization.
* Add: Settings page.
* Add: Option to rename the slug or not (default: yes).

= 0.8 =
* Fix: Works with WP 3.5.
* Change: Update the links in DB directly.
* Fix: number of flagged media not updated straight after the mass rename.
* Fix: the "file name" in the media info was empty.
* Fix: SQL optimization & memory usage huge improvement.

= 0.5 =
* Add: New view "To be renamed" in the Media Library.
* Add: a nice counter to show the number of files that need to be renamed.
* Fix: the previous update (0.4) was actually not containing all the changes.

= 0.4 =
* Support for WPML
* Support for Retina plugins such as WP Retina 2x
* Adds a '-' between the filename and counter in case of similar files
* Mark the media as to be renamed when its name is changed outside the Media Library (avoid all the issues we had before)
* The GUID is now updated using the URL of the images and not the post ID + title (http://wordpress.org/support/topic/plugin-media-file-renamer-incorrect-guid-fix-serious-bug?replies=2#post-2239192).
* Double-check before physically renaming the files.

= 0.3 =
* Corrections + improvements.
* Handles well the 'special cases' now.
* Tiny corrections.

= 0.1 =
* First release.
