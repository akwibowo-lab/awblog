<?php

test('home page returns 200 and displays Akut Wibowo', function () {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('Akut Wibowo');
});
