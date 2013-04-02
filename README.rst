embedly-php
===========

A PHP library for using the Embedly API.  To find out what Embedly is all
about, please visit http://embed.ly.  To see our api documentation, visit
http://api.embed.ly/docs.

Requirements
^^^^^^^^^^^^

You will need at least PHP version 5.3 with curl enabled. Behat  and PHPUnit
are required to run the test suite. Pear is recommended.

Installing
^^^^^^^^^^

To install::

1. Download the `composer.phar` executable or use the installer.
::
    curl -sS https://getcomposer.org/installer | php
2. Create a composer.json defining your dependencies. Note that this example is a short version for applications that are not meant to be published as a packages themselves.
::
    {
      "require": [
        "embedly/embedly-php"
      ]
    }
3. Run Composer: ``php composer.phar install``

Examples
^^^^^^^^

::

  <?php
  require_once('Embedly/src/Embedly/Embedly.php');  // if using pear
  // require_once('src/Embedly/Embedly.php');  // if using source

  $api = new Embedly\Embedly(array('user_agent' => 'Mozilla/5.0 (compatible; mytestapp/1.0)'));

  // Single url
  $objs = $api->oembed('http://www.youtube.com/watch?v=sPbJ4Z5D-n4&feature=topvideos');
  print_r($objs);

  // Multiple urls
  $obj = $api->oembed(array(
      'urls' => array(
          'http://www.youtube.com/watch?v=sPbJ4Z5D-n4&feature=topvideos',
          'http://twitpic.com/3yr7hk'
      )
  ));
  print_r($obj);

  // Call with pro (you'll need a real key)
  $pro = new Embedly_API(array(
      'key' => 'xxxxxxxxxxxxxxxx',
      'user_agent' => 'Mozilla/5.0 (compatible; mytestapp/1.0)'
  ));
  $objs = $pro->preview(array(
      'urls' => array(
          'http://www.guardian.co.uk/media/2011/jan/21/andy-coulson-phone-hacking-statement',
          'http://hn.embed.ly'
      )
  ));
  print_r($objs);

Development
^^^^^^^^^^^
::

  git clone git://github.com/embedly/embedly-php.git
  curl -sS https://getcomposer.org/installer | php
  php composer.phar install
  export EMBEDLY_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxx
  bin/behat

Note on Patches/Pull Requests
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

* Fork the project.
* Make your feature addition or bug fix.
* Add tests for it. This is important so I don't break it in a
  future version unintentionally.
* Commit, do not mess with rakefile, version, or history.  (if you want to have
  your own version, that is fine but bump version in a commit by itself I can
  ignore when I pull)
* Send me a pull request. Bonus points for topic branches.

Copyright
^^^^^^^^^

Copyright (c) 2011 Embed.ly, Inc. See MIT-LICENSE for details.
