# Sapient bundle for Symfony

[![Build Status](https://travis-ci.org/lepiaf/sapient-bundle.svg?branch=master)](https://travis-ci.org/lepiaf/sapient-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lepiaf/sapient-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lepiaf/sapient-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/lepiaf/sapient-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lepiaf/sapient-bundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/f6279110-ac35-47e3-9439-3416ece59667/mini.png)](https://insight.sensiolabs.com/projects/f6279110-ac35-47e3-9439-3416ece59667)

[Sapient](https://github.com/paragonie/sapient) is a toolkit to secure API exchange. 

```text
Sapient allows you to quickly and easily add application-layer cryptography to your API requests and responses.
```

This bundle wrap this toolkit and integrate it in Symfony in an easy way.

Main abilities are:
* Sign response
* Seal response
* Verify a response from another API

## Demo

You can check out this project and run demo locally [sapient-bundle-demo](https://github.com/lepiaf/sapient-bundle-demo)

## How to install for Symfony 4

If you use Symfony 4, a recipe exists and it will install all automatically.

Follow instructions in [https://symfony.sh/r/github.com/symfony/recipes-contrib/355](https://symfony.sh/r/github.com/symfony/recipes-contrib/355)

## How to install for Symfony below 4 or without recipe

```bash
composer require lepiaf/sapient-bundle
```

Enable bundle in you `AppKernel.php`

```php
<?php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new lepiaf\SapientBundle\SapientBundle()
        );
        
        return $bundles;
    }
}
```

Then run a command to generate key-pair and add config displayed in `config.yml`

```bash
bin/console sapient:configure
```
