<?php

namespace Bramdevries\Oauth\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Slack extends AbstractProvider
{
    /**
     * @var string
     */
    protected $domain = 'https://slack.com';

    /**
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->domain.'/oauth/authorize';
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->domain.'/api/oauth.access';
    }

    /**
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->domain.'/api/auth.test?token='.$token->getToken();
    }

    /**
     * @return array
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * @param ResponseInterface $response
     * @param array|string $data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!$data['ok'] || $response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['error'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * @param array $response
     * @param AccessToken $token
     *
     * @return ResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new ResourceOwner($response);
    }

}
