<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    public function testListusers()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/admin/listUsers');
    }

}
