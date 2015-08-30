<?php

namespace Bramdevries\Oauth\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;
use Mockery as m;

class SlackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Slack
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Slack([
            'clientId' => 'foo',
            'clientSecret' => 'bar',
            'redirectUri' => 'none',
        ]);
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes()
    {
        $options = ['scope' => [uniqid(), uniqid()]];
        $url = $this->provider->getAuthorizationUrl($options);
        $this->assertContains(urlencode(implode(',', $options['scope'])), $url);
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        $this->assertEquals('/oauth/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];
        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);
        $this->assertEquals('/api/oauth.access', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');

        $response->shouldReceive('getBody')->andReturn('{"ok":"true", "scope":"identify,read,post", "access_token": "mock_access_token"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testUserData()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $response->shouldReceive('getBody')->andReturn('{"ok": true, "url": "https:\/\/myteam.slack.com\/", "team": "My Team", "user": "cal", "team_id": "T1234", "user_id": "U1234"}');

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->andReturn($response);

        $this->provider->setHttpClient($client);
        $token = m::mock('League\OAuth2\Client\Token\AccessToken');
        $token->shouldReceive('getToken')->andReturn('foo');

        $user = $this->provider->getResourceOwner($token);
        $this->assertEquals('U1234', $user->getId());
        $this->assertEquals([
            'ok' =>  true,
            'url' => 'https://myteam.slack.com/',
            'team' => 'My Team',
            'user' => 'cal',
            'team_id' => 'T1234',
            'user_id' => 'U1234',
        ], $user->toArray());
    }

    public function testCanThrowException()
    {
        $this->setExpectedException('League\OAuth2\Client\Provider\Exception\IdentityProviderException', 'code_already_used');

        $response = m::mock('Psr\Http\Message\ResponseInterface');

        $response->shouldReceive('getBody')->andReturn('{"ok":false, "error":"code_already_used"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }
}
