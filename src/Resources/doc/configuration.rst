Configuration
=============

We will see 2 differents uses case:

* Sign only response of your API. Each client are free to check signature.
* Sign and seal response. Only the client who did request can decrypt content of response and can verify the signature.

Before diving deep, let assume we have an API Alice and a client Bob. API Alice and client Bob have a key pair to sign
and seal (see :doc:`installation` part). Client Bob will do request to API Alice in order to get some information.

.. _sign-response-only:

Sign response only
------------------

Go to configuration file and open it. After installation, you only have a key pair for ``sign`` and ``seal``.
As you only want to ``sign``, you can delete ``seal`` configuration.

.. code-block:: yaml

    sapient:
        sign:
            public: 'G3zo5Zub2o-eyp-g3GYb9JXEzdtIqmFdDOvU5PV6hBk='
            private: 'giP81DlS_R3JL4-UnSVbn2I5lm9abv8vA7aLuEdOUB4bfOjlm5vaj57Kn6DcZhv0lcTN20iqYV0M69Tk9XqEGQ=='
            name: 'api-alice'
        sealing_public_keys: ~
        verifying_public_keys: ~

Do a request and you will see your response in clear text. Then check headers of response.

.. code-block:: http

    HTTP/1.1 200 OK
    Host: localhost:8000
    Connection: close
    X-Powered-By: PHP/7.2.4-1+ubuntu16.04.1+deb.sury.org+1
    Cache-Control: no-cache, private
    Date: Sat, 12 May 2018 19:00:19 +0200, Sat, 12 May 2018 17:00:19 GMT
    content-type: application/json
    Body-Signature-Ed25519: 6sHYDSKwx05QNDe-s2a1tBXxKw2JZxLZwUBpLojEQpqzcGEU1XcaqdaG9_FQTbVkeSa_25vSak8MJcZ8RaoaAg==
    Sapient-Signer: api-alice

Two new headers appear:

* Body-Signature-Ed25519 is signature of response. It is used by Sapient library to verify response with public key.
* Sapient-Signer: name of who sign this response. It is usefull when client call more than one API.

For now we have API Alice who sign all their response. It is good but not usefull for now. Let's configure client Bob
to verify signature.

API Alice must give her sign public key to client Bob. As shown in configuration above, it is ``G3zo5Zub2o-eyp-g3GYb9JXEzdtIqmFdDOvU5PV6hBk=``.
**Do not give private key**, hence the name, it is private.

Open client Bob configuration file and add API Alice public key.

.. code-block:: yaml

    sapient:
        sealing_public_keys: ~
        verifying_public_keys:
            -
                key: 'G3zo5Zub2o-eyp-g3GYb9JXEzdtIqmFdDOvU5PV6hBk='
                name: 'api-alice'

I've added API Alice sign public key in ``verifying_public_keys`` configuration. It must have the key and name
of signer. Here it is ``api-alice``.

Client Bob use Guzzle to request API Alice. Sapient bundle comes with Guzzle middleware to make verification easier.
You need to enable it.

.. code-block:: yaml

    guzzle_middleware:
        verify: true

Here is the final configuration of client Bob.

.. code-block:: yaml

    sapient:
        guzzle_middleware:
            verify: true
        sealing_public_keys: ~
        verifying_public_keys:
            -
                key: 'G3zo5Zub2o-eyp-g3GYb9JXEzdtIqmFdDOvU5PV6hBk='
                name: 'api-alice'

Now, every time you will request API Alice, it will verify every signature. If signature cannot be verifyed,
an exception will raise. It can be a misconfiguration or an man-in-the-middle.

Sign and seal response
----------------------

This is the most usefull usecase. It sign and seal the response. Only the requester can decrypt the
content of the response. It use ``XChaCha20-Poly1305`` algorithm to encrypt and ``ED25519`` for signature.

Follow part :ref:`sign-response-only` first. In this part, we will configure API Alice to encrypt response
for client Bob.

In client Bob configuration file, generate a seal key pair. You can do it easily with ``bin/console sapient:configure``.
Take only part of seal.

.. code-block:: yaml

    sapient:
        seal:
            public: 'M2SMMPHg9NOXoX3NgzlWY8iTheyu8qSovnTZpAlIGB0='
            private: 'FzyiZAbEuquHUXt-YNF6WOXFB6CVBpyz2ocMMaT0FK8='
        guzzle_middleware:
            verify: true
        sealing_public_keys: ~
        verifying_public_keys:
            -
                key: 'G3zo5Zub2o-eyp-g3GYb9JXEzdtIqmFdDOvU5PV6hBk='
                name: 'api-alice'

As mentioned in introduction of this part, API Alice will encrypt response. Client Bob use guzzle and Sapient bundle
has a middlware to decrypt response. Enable it.

.. code-block:: yaml

    sapient:
        seal:
            public: 'M2SMMPHg9NOXoX3NgzlWY8iTheyu8qSovnTZpAlIGB0='
            private: 'FzyiZAbEuquHUXt-YNF6WOXFB6CVBpyz2ocMMaT0FK8='
        guzzle_middleware:
            verify: true
            unseal: true
        sealing_public_keys: ~
        verifying_public_keys:
            -
                key: 'G3zo5Zub2o-eyp-g3GYb9JXEzdtIqmFdDOvU5PV6hBk='
                name: 'api-alice'

Then, update configuration of Guzzle client to add a new header ``Sapient-Requester`` . API Alice must now which key it should use
to encrypt response.

.. code-block:: yaml

    csa_guzzle:
        profiler: '%kernel.debug%'
        clients:
            default: ~
            api:
                config:
                    base_uri: 'http://localhost:8000'
                    headers:
                      'Sapient-Requester': 'client-bob'

Now we are done in client Bob configuration. Before updating configuration of API Alice, copy seal public key
of client Bob.

In API Alice, add seal public key of client Bob in ``sealing_public_keys`` configuration.

.. code-block:: yaml

    sapient:
        sign:
            public: 'G3zo5Zub2o-eyp-g3GYb9JXEzdtIqmFdDOvU5PV6hBk='
            private: 'giP81DlS_R3JL4-UnSVbn2I5lm9abv8vA7aLuEdOUB4bfOjlm5vaj57Kn6DcZhv0lcTN20iqYV0M69Tk9XqEGQ=='
            name: 'api-alice'
        sealing_public_keys:
            -
                name: 'client-bob'
                key: 'M2SMMPHg9NOXoX3NgzlWY8iTheyu8qSovnTZpAlIGB0='
        verifying_public_keys: ~

Configuration is done for API Alice.

Every time client Bob will request API Alice, API Alice will encrypt and sign response. Then, client
Bob receive response and pass to Guzzle middleware. It decrypt and verify signature. If everything is ok,
your controller/service will use data as usual. Else it will raise an exception.

To get more information, check `library documentation <https://github.com/paragonie/sapient>`_. Sapient is available
in container and you can use more functionality.