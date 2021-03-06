# Sapient bundle for Symfony

[![Build Status](https://travis-ci.org/lepiaf/sapient-bundle.svg?branch=master)](https://travis-ci.org/lepiaf/sapient-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lepiaf/sapient-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lepiaf/sapient-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/lepiaf/sapient-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lepiaf/sapient-bundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/f6279110-ac35-47e3-9439-3416ece59667/mini.png)](https://insight.sensiolabs.com/projects/f6279110-ac35-47e3-9439-3416ece59667)

[Sapient](https://github.com/paragonie/sapient) is a toolkit to secure API exchange. 

HTTPS encrypts the transmission channel and its content indirectly. But there is one weak link in 
this process: the certification authority. This organization, in charge of providing certificates, 
can be attacked, and fake certificates can be generated to divert traffic to the attacker's server.

Sapient allows you to quickly and easily add application-layer cryptography to your API requests and responses.

![Full use case](src/Resources/doc/images/sapient-workflow.jpg)

This bundle wrap this toolkit and integrate it in Symfony in an easy way.

Main abilities are:
* Sign and verify response
* Seal and unseal response
* Sign and verify request
* Seal and unseal request

## Demo

You can check out this project and run demo locally [sapient-bundle-demo](https://github.com/lepiaf/sapient-bundle-demo)

## Documentation

Follow documentation [http://sapient-bundle.readthedocs.io/en/latest/](http://sapient-bundle.readthedocs.io/en/latest/)
or `src/Resources/doc/index.rst`

## References

* [Hardening Your PHP-Powered APIs with Sapient](https://paragonie.com/blog/2017/06/hardening-your-php-powered-apis-with-sapient)
* [The 2018 Guide to Building Secure PHP Software](https://paragonie.com/blog/2017/12/2018-guide-building-secure-php-software)
