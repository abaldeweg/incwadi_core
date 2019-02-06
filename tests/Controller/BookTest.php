<?php

/*
 * This script is part of baldeweg/incwadi-core
 *
 * Copyright 2019 André Baldeweg <kontakt@andrebaldeweg.de>
 */

namespace Baldeweg\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookTest extends WebTestCase
{
    public function testScenario()
    {
        // new
        $action = 'new';
        $request = $this->request($action, 'POST', [
            'title' => 'title',
            'author' => 'author',
            'genre' => 1,
            'price' => '1.00',
            'stocked' => true
        ]);

        $this->assertTrue(isset($request->id));
        $this->assertInternalType('integer', $request->id);
        $this->assertInternalType('integer', $request->added);
        $this->assertEquals('title', $request->title);
        $this->assertEquals('author', $request->author);
        $this->assertEquals(1, $request->genre);
        $this->assertEquals('1.00', $request->price);
        $this->assertEquals('EUR', $request->currency);
        $this->assertTrue($request->stocked);

        $id = $request->id;

        // edit
        $action = 'edit';
        $request = $this->request($action . '/' . $id, 'POST', [
            'title' => 'book',
            'author' => 'authors',
            'genre' => 2,
            'price' => '2.00',
            'stocked' => true
        ]);

        $this->assertTrue(isset($request->id));
        $this->assertInternalType('integer', $request->id);
        $this->assertInternalType('integer', $request->added);
        $this->assertEquals('book', $request->title);
        $this->assertEquals('authors', $request->author);
        $this->assertEquals(2, $request->genre);
        $this->assertEquals('2.00', $request->price);
        $this->assertEquals('EUR', $request->currency);
        $this->assertTrue($request->stocked);

         // show
        $request = $this->request($id, 'GET');

        $this->assertTrue(isset($request->id));
        $this->assertInternalType('integer', $request->id);
        $this->assertInternalType('integer', $request->added);
        $this->assertEquals('book', $request->title);
        $this->assertEquals('authors', $request->author);
        $this->assertEquals(2, $request->genre);
        $this->assertEquals('2.00', $request->price);
        $this->assertEquals('EUR', $request->currency);
        $this->assertTrue($request->stocked);
    }

    protected function request($action, $method = 'GET', $content = [])
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'password'
        ]);

        $crawler = $client->request(
            $method,
            '/book/' . $action,
            [],
            [],
            [],
            json_encode($content)
        );

        $this->assertTrue($client->getResponse()->isSuccessful(), 'Unexpected HTTP status code for ' . $method . ' /book/' . $action);

        return json_decode($client->getResponse()->getContent());
    }
}
