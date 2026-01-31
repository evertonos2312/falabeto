<?php

use Database\Seeders\PlanSeeder;

it('the application returns a successful response', function () {
    $this->seed(PlanSeeder::class);

    $this->get('/')
        ->assertStatus(200);
});
