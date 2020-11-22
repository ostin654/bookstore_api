<?php


namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BookControllerTest extends WebTestCase
{
    public function testSearch(): void
    {
        $client = static::createClient();

        $client->request('GET', '/en/book/search?search=Eugene');
        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('status', $data);
        self::assertEquals(Response::HTTP_OK, $data['status']);
        self::assertArrayHasKey('books', $data);
        self::assertIsArray($data['books']);
        self::assertCount(1, $data['books']);
        self::assertEquals('Eugene Onegin', $data['books'][0]['name']);

        $client->request('GET', '/en/book/search?bad=1');
        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('status', $data);
        self::assertEquals(Response::HTTP_BAD_REQUEST, $data['status']);
        self::assertArrayHasKey('error', $data);
    }
}