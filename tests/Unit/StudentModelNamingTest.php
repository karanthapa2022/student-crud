<?php

namespace Tests\Unit;

use App\Models\Student;
use Tests\TestCase;

class StudentModelNamingTest extends TestCase
{
    public function test_student_model_file_uses_correct_case(): void
    {
        $file = (new \ReflectionClass(Student::class))->getFileName();

        $this->assertNotFalse($file);
        $this->assertSame('Student.php', basename($file));
    }
}
