Feature: OEmbed

    As an embedly user
    I want to call the the embedly api
    Because I want and oembed for a specific url

    Scenario Outline: Get the provider_url
        Given an embedly api with key
        When oembed is called with the <url> URL
        Then the provider_url should be <provider_url>

        Examples:
            | url                                                          | provider_url            |
            | http://www.scribd.com/doc/13994900/Easter                    | http://www.scribd.com/  |
            | http://www.scribd.com/doc/28452730/Easter-Cards              | http://www.scribd.com/  |
            | http://www.youtube.com/watch?v=Zk7dDekYej0                   | http://www.youtube.com/ |
            | http://twitpic.com/4yri3n                                    | http://twitpic.com      |


    Scenario Outline: Get the types
        Given an embedly api with key
        When oembed is called with the <url> URL
        Then the type should be <type>

        Examples:
            | url                                                          | type  |
            | http://www.scribd.com/doc/13994900/Easter                    | rich  |
            | http://www.scribd.com/doc/28452730/Easter-Cards              | rich  |
            | http://www.youtube.com/watch?v=Zk7dDekYej0                   | video |
            | http://twitpic.com/4yri3n                                    | photo |


    Scenario Outline: Get the provider_url with force flag
        Given an embedly api with key
        When oembed is called with the <url> URL and force flag
        Then the provider_url should be <provider_url>

        Examples:
            | url                                                          | provider_url            |
            | http://www.youtube.com/watch?v=Zk7dDekYej0                   | http://www.youtube.com/ |


    Scenario Outline: Get multiple provider_urls
        Given an embedly api with key
        When oembed is called with the <urls> URLs
        Then provider_url should be <provider_urls>

        Examples:
            | urls                                                                                      | provider_urls                                 |
            | http://www.scribd.com/doc/13994900/Easter,http://www.scribd.com/doc/28452730/Easter-Cards | http://www.scribd.com/,http://www.scribd.com/ |
            | http://www.youtube.com/watch?v=Zk7dDekYej0,http://yfrog.com/h8ir0hlj                      | http://www.youtube.com/,http://yfrog.com      |


    Scenario Outline: Get the provider_url with pro
        Given an embedly api with key
        When oembed is called with the <url> URL
        Then the provider_url should be <provider_url>

        Examples:
            | url                                                                              | provider_url               |
            | http://blog.embed.ly/bob                                                         | http://blog.embed.ly/      |
            | http://blog.doki-pen.org/cassandra-rules                                         | http://blog.doki-pen.org/  |
            | http://www.guardian.co.uk/media/2011/jan/21/andy-coulson-phone-hacking-statement | http://www.guardian.co.uk/ |


    Scenario Outline: Attempt to get 404 or 401 URL
        Given an embedly api with key
        When oembed is called with the <url> URL
        Then type should be error
        And error_code should be <errcode>
        And type should be <types>

        Examples:
            | url                                                           | errcode   | types |
            | http://www.youtube.com/watch/is/a/bad/url                     | 404       | error |
            | http://www.scribd.com/doc/zfldsf/asdfkljlas/klajsdlfkasdf     | 404       | error |

    Scenario Outline: Attempt multi get 404 or 401 URLs
        Given an embedly api with key
        When oembed is called with the <urls> URLs
        Then error_code should be <errcode>
        And type should be <types>

        Examples:
            | urls                                                                             | errcode | types       |
            | http://www.youtube.com/watch/a/bassd/url,http://www.youtube.com/watch/ldf/asdlfj | 404,404 | error,error |
            | http://www.scribd.com/doc/lsbsdlfldsf/kl,http://www.scribd.com/doc/zasdf/asdfl   | 404,404 | error,error |
            | http://www.youtube.com/watch/zzzzasdf/kl,http://yfrog.com/h8ir0hlj               | 404,    | error,photo |
            | http://yfrog.com/h8ir0hlj,http://www.scribd.com/doc/asdfasdfasdf                 | ,404    | photo,error |

