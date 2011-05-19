embedly-php
===========

A PHP library for using the Embedly API.  To find out what Embedly is all about, please
visit http://embed.ly.  To see our api documentation, visit
http://api.embed.ly/docs.

Installing
^^^^^^^^^^

To install::

  sudo pear channel-discover dokipen.github.com/pear
  sudo pear install channel://dokipen.github.com/pear/Embedly-0.1.0

Examples
^^^^^^^^

::

  <?php
  require_once('src/Embedly/Embedly.php');

  $api = new Embedly\Embedly(array('user_agent' => 'Mozilla/5.0 (compatible; mytestapp/1.0)'));

  // Single url
  $objs = $api->oembed('http://www.youtube.com/watch?v=sPbJ4Z5D-n4&feature=topvideos');
  print_r($objs);

  // Multiple urls
  $objs = $api->oembed(array(
      'urls' => array(
          'http://www.youtube.com/watch?v=sPbJ4Z5D-n4&feature=topvideos',
          'http://twitpic.com/3yr7hk'
      )
  ));
  print_r($objs);

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
  sudo pear channel-discover pear.everzet.com
  sudo pear install channel://pear.everzet.com/behat-0.3.7
  sudo pear channel-discover pear.phpunit.de
  sudo pear install channel://pear.phpunit.de/PHPUnit
  # real key below, for pro tests
  export EMBEDLY_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxx
  behat

Note on Patches/Pull Requests
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

* Fork the project.
* Make your feature addition or bug fix.
* Add tests for it. This is important so I don't break it in a
  future version unintentionally.
* Commit, do not mess with rakefile, version, or history.
  (if you want to have your own version, that is fine but bump version in a commit by itself I can ignore when I pull)
* Send me a pull request. Bonus points for topic branches.

Copyright
^^^^^^^^^

Copyright (c) 2011 Embed.ly, Inc. See MIT-LICENSE for details.
