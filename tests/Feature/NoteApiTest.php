<?php

namespace Tests\Feature;

use Tests\TestCase;

class NoteApiTest extends TestCase
{
    public function test_notes_index_returns_ok()
    {
        $response = $this->get('/api/notes');
        $response->assertStatus(200);
    }
}
