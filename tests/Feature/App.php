<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

test('the application returns a successful response', function () {
    $response = $this->get('/lonnsberegner');

    $response->assertStatus(200);
});
