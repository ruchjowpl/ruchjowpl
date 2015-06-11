<?php
namespace RuchJow\AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Frontend Controller Test
 */
class FrontendControllerTest extends WebTestCase
{
    /**
     * Test index action
     */
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
