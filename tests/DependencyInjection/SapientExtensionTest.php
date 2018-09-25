<?php

namespace Tests\lepiaf\SapientBundle\EventSubscriber;

use lepiaf\SapientBundle\DependencyInjection\SapientExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class SapientExtensionTest extends TestCase
{
    public function testLoadEmptyConfiguration()
    {
        $container = $this->loadFileAndCompileContainer('empty.yml');

        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\Command\GenerateConfigurationCommand'));
        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\Service\PublicKeyGetter'));
        $this->assertTrue($container->hasDefinition('ParagonIE\Sapient\Sapient'));
        $this->assertTrue($container->hasDefinition('Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory'));
        $this->assertTrue($container->hasDefinition('Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory'));
    }

    public function testLoadBasicConfiguration()
    {
        $container = $this->loadFileAndCompileContainer('basic.yml');

        $this->assertSame('G3zo5Zub2o-eyp-g3GYb9JXEzdtIqmFdDOvU5PV6hBk=', $container->getParameter('sapient.sign.public'));
        $this->assertSame('giP81DlS_R3JL4-UnSVbn2I5lm9abv8vA7aLuEdOUB4bfOjlm5vaj57Kn6DcZhv0lcTN20iqYV0M69Tk9XqEGQ==', $container->getParameter('sapient.sign.private'));
        $this->assertSame('api-alice', $container->getParameter('sapient.sign.host'));

        $this->assertSame('tquhje8C_hNdd85R-CzVq7n7MOLqc5h11GJv7Vo7fgc=', $container->getParameter('sapient.seal.public'));
        $this->assertSame('NoxnlCvhxl8NRfCgIhuxm95IE1Y9QFUHMuvDkrWrnQ4=', $container->getParameter('sapient.seal.private'));

        // all is disabled
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SignResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\UnsealRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\RequesterHeaderMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SealRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SignRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\UnsealResponseMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\VerifyResponseMiddleware'));
    }

    public function testLoadSignConfiguration()
    {
        $container = $this->loadFileAndCompileContainer('sign_response.yml');

        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SignResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\UnsealRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\RequesterHeaderMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SealRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SignRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\UnsealResponseMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\VerifyResponseMiddleware'));
    }

    public function testLoadSignAndSealConfiguration()
    {
        $container = $this->loadFileAndCompileContainer('sign_and_seal_response.yml');

        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SignResponseSubscriber'));
        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\UnsealRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\RequesterHeaderMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SealRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SignRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\UnsealResponseMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\VerifyResponseMiddleware'));
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\ConfigurationRequiredException
     * @expectedExceptionMessage You must enable "sign" option with "sign.response" as true before using "seal.response" feature.
     */
    public function testLoadSealWithoutSignConfiguration()
    {
        $this->loadFileAndCompileContainer('seal_without_sign_response.yml');
    }

    public function testLoadVerifyRequestConfiguration()
    {
        $container = $this->loadFileAndCompileContainer('verify_request.yml');

        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SignResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\UnsealRequestSubscriber'));
        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\RequesterHeaderMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SealRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SignRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\UnsealResponseMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\VerifyResponseMiddleware'));
    }

    public function testLoadUnsealRequestConfiguration()
    {
        $container = $this->loadFileAndCompileContainer('unseal_request.yml');

        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SignResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber'));
        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\UnsealRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\RequesterHeaderMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SealRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SignRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\UnsealResponseMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\VerifyResponseMiddleware'));
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\ConfigurationRequiredException
     * @expectedExceptionMessage You must enable "seal" option before using "unseal_request" feature.
     */
    public function testLoadUnsealRequestWithoutSealConfiguration()
    {
        $this->loadFileAndCompileContainer('unseal_request_without_seal_response.yml');
    }

    public function testLoadGuzzleRequesterHostConfiguration()
    {
        $container = $this->loadFileAndCompileContainer('guzzle_requester_host.yml');

        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SignResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\UnsealRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber'));
        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\RequesterHeaderMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SealRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SignRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\UnsealResponseMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\VerifyResponseMiddleware'));
    }

    public function testLoadGuzzleVerifyConfiguration()
    {
        $container = $this->loadFileAndCompileContainer('guzzle_verify.yml');

        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SignResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\UnsealRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\RequesterHeaderMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SealRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SignRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\UnsealResponseMiddleware'));
        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\VerifyResponseMiddleware'));
    }

    public function testLoadGuzzleUnsealConfiguration()
    {
        $container = $this->loadFileAndCompileContainer('guzzle_unseal.yml');

        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SignResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\UnsealRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\RequesterHeaderMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SealRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SignRequestMiddleware'));
        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\UnsealResponseMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\VerifyResponseMiddleware'));
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\ConfigurationRequiredException
     * @expectedExceptionMessage You must enable "seal" option and configure a "seal.private" key before using "guzzle_middleware.unseal" feature.
     */
    public function testLoadGuzzleUnsealWithoutSealConfiguration()
    {
        $this->loadFileAndCompileContainer('guzzle_unseal_without_seal.yml');
    }

    public function testLoadGuzzleSignRequestConfiguration()
    {
        $container = $this->loadFileAndCompileContainer('guzzle_sign_request.yml');

        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SignResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\UnsealRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\RequesterHeaderMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SealRequestMiddleware'));
        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SignRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\UnsealResponseMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\VerifyResponseMiddleware'));
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\ConfigurationRequiredException
     * @expectedExceptionMessage You must enable "sign" option and configure a "sign.private" key before using "guzzle_middleware.sign_request" feature.
     */
    public function testLoadGuzzleSignRequestWithoutSignConfiguration()
    {
        $this->loadFileAndCompileContainer('guzzle_sign_request_without_sign.yml');
    }

    public function testLoadGuzzleSignSealRequestConfiguration()
    {
        $container = $this->loadFileAndCompileContainer('guzzle_sign_seal_request.yml');

        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SignResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\UnsealRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\RequesterHeaderMiddleware'));
        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SealRequestMiddleware'));
        $this->assertTrue($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\SignRequestMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\UnsealResponseMiddleware'));
        $this->assertFalse($container->hasDefinition('lepiaf\SapientBundle\GuzzleHttp\Middleware\VerifyResponseMiddleware'));
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\ConfigurationRequiredException
     * @expectedExceptionMessage You must enable "guzzle_middleware.sign_request" option before using "guzzle_middleware.seal_request" feature.
     */
    public function testLoadGuzzleSealRequestWithoutSignRequestConfiguration()
    {
        $this->loadFileAndCompileContainer('guzzle_seal_request_without_sign_request.yml');
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\ConfigurationRequiredException
     * @expectedExceptionMessage You must enable "seal" option and configure a "seal.private" key before using "guzzle_middleware.seal_request" feature.
     */
    public function testLoadGuzzleSealRequestWithoutSealConfiguration()
    {
        $this->loadFileAndCompileContainer('guzzle_seal_request_without_seal.yml');
    }

    private function loadFileAndCompileContainer($filename)
    {
        $container = new ContainerBuilder(new ParameterBag());
        $container->registerExtension(new SapientExtension());
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/Fixtures/'));
        $loader->load($filename);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());

        $container->compile();

        return $container;
    }
}
