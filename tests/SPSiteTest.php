<?php
/**
 * This file is part of the SPOIL library.
 *
 * @author     Quetzy Garcia <quetzyg@impensavel.com>
 * @copyright  2014-2016
 *
 * For the full copyright and license information,
 * please view the LICENSE.md file that was distributed
 * with this source code.
 */

namespace Impensavel\Spoil\Tests;

use Firebase\JWT\JWT;
use Http\Message\MessageFactory\GuzzleMessageFactory as MessageFactory;
use Http\Mock\Client as HttpClient;

use Impensavel\Spoil\SPSite;

class SPSiteTest extends SPTestCase
{
    /**
     * Test SPSite constructor to FAIL (invalid URL)
     *
     * @expectedException         \Impensavel\Spoil\Exception\SPRuntimeException
     * @expectedExceptionMessage  The SharePoint Site URL is invalid
     *
     * @return  void
     */
    public function testSPSiteConstructorFailInvalidUrl()
    {
        $message = new MessageFactory;
        $client = new HttpClient($message);

        new SPSite(null, [], $client, $message);
    }

    /**
     * Test SPSite constructor to PASS
     *
     * @return  SPSite
     */
    public function testSPSiteConstructorPass()
    {
        $message = new MessageFactory;
        $client = new HttpClient($message);

        // testSPSiteGetSPAccessTokenWithoutContextPass
        $client->addResponse($this->createMockResponse('access_token.json'));

        // testSPSiteGetSPAccessTokenWithContextPass
        $client->addResponse($this->createMockResponse('access_token.json'));

        // testSPSiteGetSPContextInfoPass
        $client->addResponse($this->createMockResponse('context_info.json'));

        $site = new SPSite('https://example.sharepoint.com/sites/mySite/', [
            'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE=',
        ], $client, $message);

        $this->assertInstanceOf(\Impensavel\Spoil\SPSite::class, $site);

        return $site;
    }

    /**
     * Test SPSite getSPAccessToken() method to FAIL (invalid token)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \Impensavel\Spoil\Exception\SPRuntimeException
     * @expectedExceptionMessage  Invalid SharePoint Access Token
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPAccessTokenFailInvalidToken(SPSite $site)
    {
        $site->getSPAccessToken();
    }

    /**
     * Test SPSite getSPAccessToken() method to FAIL (expired token)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \Impensavel\Spoil\Exception\SPRuntimeException
     * @expectedExceptionMessage  Expired SharePoint Access Token
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPAccessTokenFailExpiredToken(SPSite $site)
    {
        $serialized = sprintf('C:30:"Impensavel\Spoil\SPAccessToken":59:{a:3:{i:0;s:0:"";i:1;i:%d;i:2;s:13:"Europe/London";}}', time());

        $token = unserialize($serialized);

        $this->assertInstanceOf(\Impensavel\Spoil\SPAccessToken::class, $token);
        $this->assertFalse($token->hasExpired());

        $site->setSPAccessToken($token);

        // Wait 1 sec for Access Token to expire
        sleep(1);

        $this->assertTrue($token->hasExpired());
        $site->getSPAccessToken();
    }

    /**
     * Test SPSite getSPAccessToken() method to PASS (without context)
     *
     * @depends testSPSiteConstructorPass
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPAccessTokenWithoutContextPass(SPSite $site)
    {
        $site->createSPAccessToken();

        $token = $site->getSPAccessToken();

        $this->assertInstanceOf(\Impensavel\Spoil\SPAccessToken::class, $token);
    }

    /**
     * Test SPSite getSPAccessToken() method to PASS (with context)
     *
     * @depends testSPSiteConstructorPass
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPAccessTokenWithContextPass(SPSite $site)
    {
        // Dummy payload
        $payload = [
            'aud'                => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'iss'                => '00000000-0000-ffff-0000-000000000000@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'nbf'                => time(),
            'exp'                => (time() + 1800),
            'appctxsender'       => '00000000-0000-ffff-0000-000000000000@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'appctx'             => json_encode([
                'CacheKey'                => '3+$xWJW69Xy+k5%KD=Tp6<NYT=8:qY{H31w7Q8a6+=xi5Jq8(<m6bGz.8S6f*0$',
                'NextCacheKey'            => null,
                'SecurityTokenServiceUri' => 'https://accounts.accesscontrol.windows.net/tokens/OAuth/2',
            ]),
            'refreshtoken'       => '73xXmf0RGc4YvH0VErnCstTH6X925QXC',
            'isbrowserhostedapp' => true,
        ];

        $access_token = JWT::encode($payload, 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE=');

        $site->createSPAccessToken($access_token);

        $this->assertInstanceOf(\Impensavel\Spoil\SPAccessToken::class, $site->getSPAccessToken());
    }

    /**
     * Test SPSite setSPAccessToken() method to FAIL (invalid token)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \Impensavel\Spoil\Exception\SPRuntimeException
     * @expectedExceptionMessage  Expired SharePoint Access Token
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteSetSPAccessTokenFailInvalidToken(SPSite $site)
    {
        $token = unserialize('C:30:"Impensavel\Spoil\SPAccessToken":50:{a:3:{i:0;s:0:"";i:1;i:0;i:2;s:13:"Europe/London";}}');

        $this->assertInstanceOf(\Impensavel\Spoil\SPAccessToken::class, $token);
        $this->assertTrue($token->hasExpired());

        $site->setSPAccessToken($token);
    }

    /**
     * Test SPSite setSPAccessToken() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteSetSPAccessTokenPass(SPSite $site)
    {
        $token = unserialize('C:30:"Impensavel\Spoil\SPAccessToken":59:{a:3:{i:0;s:0:"";i:1;i:2147483647;i:2;s:13:"Europe/London";}}');

        $this->assertInstanceOf(\Impensavel\Spoil\SPAccessToken::class, $token);
        $this->assertFalse($token->hasExpired());

        $site->setSPAccessToken($token);
    }

    /**
     * Test SPSite getSPContextInfo() method to FAIL (invalid digest)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \Impensavel\Spoil\Exception\SPRuntimeException
     * @expectedExceptionMessage  Invalid SharePoint Context Info
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPContextInfoFailInvalidDigest(SPSite $site)
    {
        $site->getSPContextInfo();
    }

    /**
     * Test SPSite getSPContextInfo() method to FAIL (expired digest)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \Impensavel\Spoil\Exception\SPRuntimeException
     * @expectedExceptionMessage  SharePoint Context Info with expired Form Digest
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPContextInfoFailExpiredDigest(SPSite $site)
    {
        $serialized = sprintf(
            'C:30:"Impensavel\Spoil\SPContextInfo":128:{a:5:{i:0;s:14:"16.0.1234.5678";i:1;a:2:{i:0;s:8:"14.0.0.0";i:1;s:8:"15.0.0.0";}i:2;N;i:3;i:%d;i:4;s:13:"Europe/Lisbon";}}',
            time()
        );
        $contextInfo = unserialize($serialized);

        $this->assertInstanceOf(\Impensavel\Spoil\SPContextInfo::class, $contextInfo);
        $this->assertFalse($contextInfo->formDigestHasExpired());

        $site->setSPContextInfo($contextInfo);

        // Wait 1 sec for Context Info Form Digest to expire
        sleep(1);

        $site->getSPContextInfo();
    }

    /**
     * Test SPSite getSPContextInfo() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPContextInfoPass(SPSite $site)
    {
        $site->createSPContextInfo();

        $contextInfo = $site->getSPContextInfo();

        $this->assertInstanceOf(\Impensavel\Spoil\SPContextInfo::class, $contextInfo);
    }

    /**
     * Test SPSite setSPContextInfo() method to FAIL (invalid digest)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \Impensavel\Spoil\Exception\SPRuntimeException
     * @expectedExceptionMessage  SharePoint Context Info with expired Form Digest
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteSetSPContextInfoInvalidDigest(SPSite $site)
    {
        $contextInfo = unserialize(
            'C:30:"Impensavel\Spoil\SPContextInfo":88:{a:5:{i:0;s:0:"";i:1;a:2:{i:0;s:0:"";i:1;s:0:"";}i:2;N;i:3;i:0;i:4;s:13:"Europe/Lisbon";}}'
        );

        $this->assertInstanceOf(\Impensavel\Spoil\SPContextInfo::class, $contextInfo);
        $this->assertTrue($contextInfo->formDigestHasExpired());

        $site->setSPContextInfo($contextInfo);
    }

    /**
     * Test SPSite setSPContextInfo() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteSetSPContextInfoPass(SPSite $site)
    {
        $original = 'C:30:"Impensavel\Spoil\SPContextInfo":128:{a:5:{i:0;s:14:"16.0.1234.5678";i:1;a:2:{i:0;s:8:"14.0.0.0";i:1;s:8:"15.0.0.0";}i:2;N;i:3;i:2147483647;i:4;s:13:"Europe/Lisbon";}}';
        $contextInfo = unserialize($original);

        $this->assertInstanceOf(\Impensavel\Spoil\SPContextInfo::class, $contextInfo);
        $this->assertFalse($contextInfo->formDigestHasExpired());

        $site->setSPContextInfo($contextInfo);

        $this->assertEquals($original, serialize($contextInfo));
    }

    /**
     * Test SPSite getConfig() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetConfigPass(SPSite $site)
    {
        $config = $site->getConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('acs', $config);
        $this->assertArrayHasKey('resource', $config);
        $this->assertArrayHasKey('client_id', $config);
        $this->assertArrayHasKey('secret', $config);
    }

    /**
     * Test SPSite getHostname() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetHostnamePass(SPSite $site)
    {
        $this->assertEquals('https://example.sharepoint.com/', $site->getHostname());
        $this->assertEquals('https://example.sharepoint.com/test/path', $site->getHostname('test/path'));
        $this->assertEquals('https://example.sharepoint.com/test/path/', $site->getHostname('test/path/'));
        $this->assertEquals('https://example.sharepoint.com/test/path', $site->getHostname('/test/path'));
        $this->assertEquals('https://example.sharepoint.com/test/path/', $site->getHostname('/test/path/'));
    }

    /**
     * Test SPSite getPath() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetPathPass(SPSite $site)
    {
        $this->assertEquals('/sites/mySite/', $site->getPath());
        $this->assertEquals('/sites/mySite/test/path', $site->getPath('test/path'));
        $this->assertEquals('/sites/mySite/test/path/', $site->getPath('test/path/'));
        $this->assertEquals('/sites/mySite/test/path', $site->getPath('/test/path'));
        $this->assertEquals('/sites/mySite/test/path/', $site->getPath('/test/path/'));
    }

    /**
     * Test SPSite getURL() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetUrlPass(SPSite $site)
    {
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/', $site->getUrl());
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/test/path', $site->getUrl('test/path'));
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/test/path/', $site->getUrl('test/path/'));
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/test/path', $site->getUrl('/test/path'));
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/test/path/', $site->getUrl('/test/path/'));
    }

    /**
     * Test SPSite getLogoutURL() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetLogoutUrlPass(SPSite $site)
    {
        $this->assertNotFalse(filter_var($site->getLogoutUrl(), FILTER_VALIDATE_URL));
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/_layouts/SignOut.aspx', $site->getLogoutUrl());
    }
}
