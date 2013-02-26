# Datalinks

Datalinks is an old PHP app that I wrote back in 2007. I'm putting it here because there's still a theoretical
possibility that someone will find some use for it, although I don't have much hope for that. I'm not planning to
maintain it on work on it at all, so if you do find it interesting, make your own fork and I'll link to it here.

The name comes from one of the best games I've ever played,
[Alpha Centauri](http://en.wikipedia.org/wiki/Sid_Meier's_Alpha_Centauri) (a sequel of Civilization). "The Datalinks"
was the name for a future version of the Internet.

<a href="http://jsuder.github.com/datalinks/datalinks-screenshot.png"><img src="http://jsuder.github.com/datalinks/datalinks-screenshot.png" width="500"></a>

## Introduction

Datalinks is a web application for storing a collection of personal bookmarks, written in PHP. It allows you to
store all your bookmarks in one place and access them from any computer. Links are assigned to categories, which can be
arranged in a tree hierarchy. Information about links and categories is stored in two tables in a MySQL database.

Through `index.php` page you can add, edit and delete both categories and links, and you can move links and categories
from one category to another. Most of these actions are done through AJAX requests. To make any changes in the database
you have to log in (the password is set in a config file). You can also search the database for links and categories
containing a given phrase.

Normal categories use a blue folder icon and are visible for all visitors. If you want to hide some links, you can use
one of two other kinds of categories: private (red) are visible on the lists, but regular users can't see their
contents, while hidden (grey) aren't shown anywhere at all. Links and categories assigned to private or hidden
categories (and their subcategories, and sub-subcategories, etc.) also don't show up on the search results page.

When you delete a category, by default all links from that category and its subcategories aren't deleted, but are moved
to its parent category instead. If you want to remove them together with the category, select the "Delete links too"
option.


## Requirements

* WWW Server (e.g. Apache) with PHP and MySQL PHP module (tested with Apache 2.0.54 + PHP 4.3.10)
* MySQL database – tested with v. 4.1.11

Datalinks has been tested in Firefox 2, IE 6 and 7, and Opera 9.


## Installation

Installation should be pretty simple. First, download
[the latest zip](https://github.com/downloads/jsuder/datalinks/datalinks-2.0.1.zip) and unpack all files to a chosen
directory. Make sure that the `config.php` file is writable for PHP scripts (you may need to give full permissions to it
to all users, just for the installation). Then open `install/install.php` in a browser, fill all the fields in the form
and click "Install". The script should create two tables, one for categories and one for links, put one category (the
root category) in the categories table, and update the `config.php` file. If everything is OK, a message "Installation
successful!" will appear and you will be asked to delete the `install` directory. Then you can go to `index.php`, log in
using the password you have given during the installation, and start adding new categories and links.

If you want to change your password or other settings after installation, you can do that by editing `config.php` file.
You should also make sure that this file won't be accessible for other users of your server (e.g. on Unix systems set
its access rights to 600 or `rw-------`), unless the Apache server is configured in such a way that the application
doesn't work if such permissions are set.

Alternatively, if you want to create the tables and config file yourself – the table definitions are stored in
`doc/tables.sql` file.

## Troubleshooting

* If you get strange HTTP errors like 403 or 500, try changing some access rights to the datalinks directory or to
`.php` files; if nothing works, consult the admin of your server (unless you're the admin, then you have a problem ;)
* If you get logged out too often, find your main PHP configuration file (`php.ini`) and increase the session file
lifetime (`session.gc_maxlifetime` option). Or consult your admin :)


## Known issues

* Javascript sorts names containing non-ascii letters in a wrong way (MySQL does it right, so reloading the page usually
helps)

## Credits

Copyright by Jakub Suder, licensed under [MIT license](blob/master/MIT-LICENSE.txt).

Libraries used:

* Simple DataBase Abstraction (Artemi Krymski, <http://www.morewhite.com/sdba>)
* Script.aculo.us (Thomas Fuchs, <http://script.aculo.us>)
* Prototype (Sergio Pereira, <http://www.prototypejs.org>)
* Nifty Cube (Alessandro Fulciniti, <http://www.html.it/articoli/niftycube/index.html>)

Icons have been "borrowed" from KDE icon theme ["Crystal SVG"](http://everaldo.com/crystal.html) by Everaldo Coelho and
from ["macfox" Firefox theme](http://www.webether.com/macfox) by Kelly Cunningham. Throbbers have been made using
[AjaxLoad generator](http://www.ajaxload.info).
