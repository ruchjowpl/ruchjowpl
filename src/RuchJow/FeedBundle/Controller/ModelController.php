<?php

namespace RuchJow\FeedBundle\Controller;

use RuchJow\PageFoundationBundle\Controller\ModelController as PageFoundationModelController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ModelController - provides basic helper functions.
 *
 * @package RuchJow\FeedBundle\Controller
 */
class ModelController extends PageFoundationModelController
{
    protected function performRequest($url, $timeout)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return new Response('', 404);
        }

        return new Response($response, $status);
    }
}