Installation
============

Before starting installation, your application must have PHP 7.2 minimum. This bundle use Sapient library
which require `libsodium` extension. Symfony 3.4 and Synfony 4 are supported.

.. _symfony4_with_recipe:
Symfony 4 with recipe
------------------------

A recipe has been created for this bundle.

Enable contrib repository in composer.

.. code-block:: bash

    composer config extra.symfony.allow-contrib true

.. code-block:: bash

    composer install lepiaf/sapient-bundle

By default, recipe will enable and generate a minimal config file. You have to run a command to initialize
configuration.

.. code-block:: bash

    bin/console sapient:configure

It will output configuration. Copy and paste it to `config/packages/sapient.yml`

.. code-block:: yaml

    sapient:
        sign:
            public: 'signing-key-public'
            private: 'signing-key-private'
            name: 'signer-name'
        seal:
            public: 'seal-key-public'
            private: 'seal-key-private'
        sealing_public_keys: ~
        verifying_public_keys: ~

Now your api is ready. Repeat this process with a client.


.. _symfony4_without_recipe:
Symfony 4 without recipe
------------------------

As usual, install it via composer

.. code-block:: bash

    composer install lepiaf/sapient-bundle

Enable it in `config/bundles.yml`

.. code-block:: php

    <?php

    return [
        lepiaf\SapientBundle\SapientBundle::class => ['all' => true],
    ];

Now bundle is registered. You can run command to generate default configuration.

.. code-block:: bash

    bin/console sapient:configure

It will output configuration. Copy and paste it to `config/packages/sapient.yml`

.. code-block:: yaml

    sapient:
        sign:
            public: 'signing-key-public'
            private: 'signing-key-private'
            name: 'signer-name'
        seal:
            public: 'seal-key-public'
            private: 'seal-key-private'
        sealing_public_keys: ~
        verifying_public_keys: ~

Now your api is ready. Repeat this process with a client.

.. _symfony34_without_recipe:
Symfony 3.4 without recipe
--------------------------

PHP 7.2 is the only requirement, it can work with symfony 3.4 and below.

Install it via composer

.. code-block:: bash

    composer install lepiaf/sapient-bundle

Enable bundle in `app/AppKernel.php`

.. code-block:: php

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

Now bundle is registered. You can run command to generate default configuration.

.. code-block:: bash

    bin/console sapient:configure

It will output configuration. Copy and paste it to `app/config/config.yml`

.. code-block:: yaml

    sapient:
        sign:
            public: 'signing-key-public'
            private: 'signing-key-private'
            name: 'signer-name'
        seal:
            public: 'seal-key-public'
            private: 'seal-key-private'
        sealing_public_keys: ~
        verifying_public_keys: ~

Now your api is ready. Repeat this process with a client.
