<?php

it('returns a successful response from homepage', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
