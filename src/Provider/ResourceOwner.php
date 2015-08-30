<?php

namespace Bramdevries\Oauth\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class ResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $details = [];

    /**
     * ResourceOwner constructor.
     *
     * @param array $details
     */
    public function __construct(array $details)
    {
        $this->details = $details;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->details['user_id'];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->details;
    }

}
