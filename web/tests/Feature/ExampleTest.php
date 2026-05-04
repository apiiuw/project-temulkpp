<?php

test('the root path redirects to reservasi', function () {
    $response = $this->get('/');

    $response->assertRedirect('/reservasi');
});

test('the reservasi page returns a successful response', function () {
    $response = $this->get('/reservasi');

    $response->assertStatus(200);
});
