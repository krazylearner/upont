<?php

namespace KI\FoyerBundle\Tests\Controller;

use KI\CoreBundle\Tests\WebTestCase;

class FoyerControllerTest extends WebTestCase
{
    public function testStatistics()
    {
        $this->client->request('GET', '/statistics/foyer');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);

        $this->client->request('GET', '/statistics/foyer/trancara');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);

        $this->connect('peluchom', 'password');
        $this->client->request('PATCH', '/users/trancara/balance', array('balance' => 20.5));
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $this->client->request('PATCH', '/users/trancara/balance', array('balance' => -20.5));
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);
    }
}