## Synopsis

This is a WordPress plugin that has been around for nearly 9 years now. It allows you to *exclude* posts that belong to certain categories from your *home page*, while still being reachable from the inner sections of your site.

This is an ad-hoc solution that aims to do **one** thing, and do it **right**, with the smallest footprint possible (hopefully). So please note the following:

* The functionality of this plugin can not be limited to a given widget on the home page. For example:

Given a the category "MyCategory" from which you want to exclude posts on the **home page**, and at the same time you want to display posts of the same "MyCategory" in a widget that is also located **in the home page**, will not be possible.

Such a functionality, in my opinion, would be overkilling and does not represent the approach taken by this plugin.

* The focus of this plugin is simplicity, performance and correctness of the code.

You can see this plugin in action the following projects:

* http://asteriskfaqs.org - Important VoIP community project.
* http://centosfaq.org    - Important community around CentOS Linux.
* http://slackware-es.com - Spanish version of the Slackware Linux project's website.

## Background story

I decided to maintain the code in Github in order to make it available to a bigger audience. I'm not necessarily an SVN fan, and the WordPress SVN repositories are not intended for development.

## Installation

1. Upload `pe-category-filter` directory to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Settings' and choose which categories' posts you want to _exclude_ from the Home Page

**If you manage your WordPress installation using composer, follow this instructions:**

1. Add the following in the `requirements` section of your `composer.json` file:

```
  "require": {
    "wpackagist-plugin/pe-category-filter":"^1.4"
  },
```

2. Add the following to the `repositories` section of your `composer.json` file:

```
  "repositories" : [
      {
          "type":"composer",
          "url":"https://wpackagist.org"
      }
  ]
```

3. Run `composer install`

**A third installation method if you are using composer is to add the Github repo:**

1. Add the following in the `requirements` section of your `composer.json` file:

```
  "require": {
      "pavel_espinal/pe-category-filter":"^1.4.0"
  },
```

2. Add the following to the `repositories` section of your `composer.json` file:

```
  "repositories" : [
      {
          "type":"git",
          "url":"https://github.com/jespinal/PE-Category-Filter.git"
      }
  ]
```

3. Run `composer install`


Note: the simplest way to install it is to search for it through the WordPress plugin search bar.
