<?php

namespace RuchJow\FacebookBundle\Services;

use Facebook\Facebook as FacebookSdk;

class Facebook
{
    /**
     * @var FacebookSdk
     */
    protected $facebook;

    public function __construct($clientId, $clientSecret, $version)
    {
        $this->facebook = new FacebookSdk([
            'app_id'                => $clientId,
            'app_secret'            => $clientSecret,
            'default_graph_version' => $version
        ]);
    }

    public function getFacebook()
    {
        return $this->facebook;
    }
}