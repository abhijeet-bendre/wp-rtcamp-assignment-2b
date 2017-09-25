===  Assignment-2b: WordPress-Contributors Plugin

* Contributors: (abhijeet-bendre)
* Requires at least: 4.8.1
* Tested up to: 4.8
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin for "Assignment-2b: WordPress-Slideshow Plugin"
From here: https://careers.rtcamp.com/wordpress-engineer/assignment/

## Description ##

###  Admin-Side:

    Addd a new metabox, labeled "Contributors" to WordPress post-editor page. This metabox will display list of authors (wordpress users) with a checkbox for each author.
    shows and add/remove images to slide show.
    
    User (author/editor/admin) may tick one or more authors name from the list.
    When post saves, states of checkboxes for author-list in "Contributors" box is saved as well.

### Front-end:

    This Plugin uses post-content filter "the_content". At the end of post, a box called "Contributors". is displayed 
    Post contributor names with their Gravatars are shown and theire names are clickable and will link to their respective "author" page.


### Installation:

1. Upload the entire `wp-rtcamp-assignment-2b` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. You will find a new metabox, labeled **Contributors** to WordPress post-editor page.


### Changelog

#### 0.1 ####
* First Version
