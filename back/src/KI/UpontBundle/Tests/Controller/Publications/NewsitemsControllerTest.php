<?php

namespace KI\UpontBundle\Tests\Controller\Publications;

use KI\UpontBundle\Tests\WebTestCase;

class NewsitemsControllerTest extends WebTestCase
{
    // On crée une ressource sur laquelle seront effectués les tests. Ne pas oublier de supprimer à la fin avec le test DELETE.
    public function testPost()
    {
        $this->client->request('POST', '/newsitems', array('name' => 'La Porte', 'textLong' => 'C\'est comme perdre', 'authorClub' => 'ki'));
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 201);
        // On vérifie que le lieu du nouvel objet a été indiqué
        $this->assertTrue($response->headers->has('Location'), $response->headers);
    }

    public function testGet()
    {
        $this->client->request('GET', '/newsitems');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);

        $this->client->request('GET', '/newsitems/la-porte');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);

        $this->client->request('GET', '/newsitems/sjoajsiohaysahais-asbsksaba7');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 404);
    }

    public function testPatch()
    {
        $this->client->request('PATCH', '/newsitems/la-porte', array('textLong' => 'ddssqdqsd'));
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $this->client->request('PATCH', '/newsitems/la-porte', array('textLong' => ''));
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 400);

        $this->client->request('PATCH', '/newsitems/sjoajsiohaysahais-asbsksaba7', array('username' => 'miam', 'email' => '123@mail.fr'));
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 404);
    }

    public function testLike()
    {
        $this->client->request('GET', '/newsitems/basdsqdqsdqck-in-black/like');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 404);

        $this->client->request('GET', '/newsitems/la-porte/unkike');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 404);

        $this->client->request('GET', '/newsitems/la-porte/like');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);

        $this->client->request('GET', '/newsitems/la-porte/dislike');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);

        $this->client->request('POST', '/newsitems/la-porte/like');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $this->client->request('GET', '/newsitems/la-porte');
        $this->assertJsonResponse($this->client->getResponse(), 200);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('like', $response);
        $this->assertArrayHasKey('dislike', $response);
        $this->assertTrue($response['like']);
        $this->assertTrue(!$response['dislike']);

        $this->client->request('POST', '/newsitems/la-porte/dislike');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $this->client->request('GET', '/newsitems/la-porte');
        $this->assertJsonResponse($this->client->getResponse(), 200);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('like', $response);
        $this->assertArrayHasKey('dislike', $response);
        $this->assertTrue(!$response['like']);
        $this->assertTrue($response['dislike']);

        $this->client->request('DELETE', '/newsitems/la-porte/like');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $this->client->request('DELETE', '/newsitems/la-porte/dislike');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);
    }

    public function testComments()
    {
        $this->client->request('GET', '/newsitems/la-porte/comments');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    public function testDelete()
    {
        $this->client->request('DELETE', '/newsitems/la-porte');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 204);

        $this->client->request('DELETE', '/newsitems/la-porte');
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 404);
    }
}
