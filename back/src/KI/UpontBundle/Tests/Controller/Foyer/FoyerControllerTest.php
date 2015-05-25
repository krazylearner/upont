<?php

namespace KI\UpontBundle\Tests\Controller\Foyer;

use KI\UpontBundle\Tests\WebTestCase;

class FoyerControllerTest extends WebTestCase
{
    public function testBalance()
    {
        $this->client->request('GET', '/foyer/balance');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    public function testStatistics()
    {
        $this->client->request('GET', '/foyer/statistics');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);

        $this->client->request('GET', '/foyer/statistics/trancara');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);
    }
}
