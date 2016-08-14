<?php

namespace Tests\KI\DvpBundle\Controller;

use Tests\KI\CoreBundle\WebTestCase;

class BasketOrdersControllerTest extends WebTestCase
{
    // On crée une ressource sur laquelle seront effectués les tests.
    // Ne pas oublier de supprimer à la fin avec le test DELETE.
    public function testPost()
    {
        // Poste une commande
        $this->client->request('POST', '/baskets/panier-moyen/order', [
            'dateRetrieve' => '2016-08-08'
            ]
        );
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);
    }

    public function testGet()
    {
        $this->client->request('GET', '/baskets-orders/alberic.trancart@eleves.enpc.fr');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);

        $this->client->request('GET', '/baskets-orders');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);

        $this->client->request('GET', '/baskets-orders/srgsegherge');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    public function testPatch()
    {
        $this->client->request(
            'PATCH',
            '/baskets/panier-moyen/order/alberic.trancart@eleves.enpc.fr',
            [
                'paid' => true,
                'dateRetrieve' => '2016-08-08',
            ]
        );
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);
    }

    public function testDelete()
    {
        $this->client->request(
            'DELETE',
            '/baskets/panier-moyen/order/alberic.trancart@eleves.enpc.fr/2016-08-08'
            );
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $this->client->request(
            'DELETE',
            '/baskets/panier-moyen/order/alberic.trancart@eleves.enpc.fr/2016-08-08'
            );
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 404);
    }
}
