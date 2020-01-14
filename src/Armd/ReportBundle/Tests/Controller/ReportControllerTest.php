<?php

namespace Armd\ReportBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportControllerTest extends WebTestCase
{
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = static::createClient();

        // Create a new entry in the database
        $crawler = $client->request('GET', '/');
        $crawler = $client->followRedirect();
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
       
        $form = $crawler->selectButton('_submit')->form(array( '_username' => 'apachay', '_password' => '123' ));
        $client->submit($form);

        $crawler = $client->followRedirect();
        $this->assertTrue(200 === $client->getResponse()->getStatusCode()); 

        $crawler = $client->request('GET', '/user_day');

        $crawler = $client->followRedirect();

        $this->assertTrue(200 === $client->getResponse()->getStatusCode());

        // $crawler = $client->followRedirect();

        // Check data in the show view
        //$this->assertTrue($crawler->filter('td:contains("Test")')->count() > 0);

        // Check the entity has been delete on the list
        //$this->assertNotRegExp('/Foo/', $client->getResponse()->getContent());
    }
}
