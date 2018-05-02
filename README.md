# Sapient bundle for Symfony

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/f6279110-ac35-47e3-9439-3416ece59667/big.png)](https://insight.sensiolabs.com/projects/f6279110-ac35-47e3-9439-3416ece59667)

[Sapient](https://github.com/paragonie/sapient) is a toolkit to secure API exchange. 

```text
Sapient allows you to quickly and easily add application-layer cryptography to your API requests and responses.
```

This bundle wrap this toolkit and integrate it in Symfony in an easy way.

Main abilities are:
* Sign response
* Seal response
* Verify a response from another API

## How to install for Symfony 4 (not yet implemented)

If you use Symfony 4, a recipe exists and it will install all automatically.

```bash
composer require lepiaf/sapient-bundle
```

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
