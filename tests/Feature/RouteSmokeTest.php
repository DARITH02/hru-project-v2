<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteSmokeTest extends TestCase
{
    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_student_overview_requires_authentication(): void
    {
        $this->get('/admin/students/overview')->assertRedirect('/login');
    }
}
