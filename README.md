# Slack Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/bramdevries/oauth2-slack.svg?style=flat-square)](https://github.com/bramdevries/oauth2-slack/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/bramdevries/oauth2-slack/master.svg?style=flat-square)](https://travis-ci.org/bramdevries/oauth2-slack)
[![Total Downloads](https://img.shields.io/packagist/dt/bramdevries/oauth2-slack.svg?style=flat-square)](https://packagist.org/packages/bramdevries/oauth2-slack)

This package provides Slack OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require bramdevries/oauth2-slack
```

## Usage

Usage is the same as The League's OAuth client, using `\League\OAuth2\Client\Provider\Slack` as the provider.

### Authorization Code Flow

```php
$provider = new League\OAuth2\Client\Provider\Slack([
    'clientId'          => '{slack-client-id}',
    'clientSecret'      => '{slack-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getNickname());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Managing Scopes

When creating your Slack authorization URL, you can specify the state and scopes your application may authorize.

```php
$options = [
    'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
    'scope' => ['identify','read','post'] // array or string
];

$authorizationUrl = $provider->getAuthorizationUrl($options);
```
If neither are defined, the provider will utilize internal defaults.

At the time of authoring this documentation, the [following scopes are available](https://api.slack.com/docs/oauth).

- identify
- read
- post
- client
- admin

## Testing

``` bash
$ ./vendor/bin/phpunit
```
## Credits

- [Bram Devries](https://github.com/bramdevries)
- [All Contributors](https://github.com/bramdevries/oauth2-slack/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/bramdevries/oauth2-slack/blob/master/LICENSE) for more information.
