<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class RoleBasedDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_roles_can_access_their_dashboard_payload(): void
    {
        $roles = ['admin', 'teacher', 'student', 'parent'];

        foreach ($roles as $role) {
            $user = User::factory()->create([
                'email' => $role . '@example.com',
                'role' => $role,
            ]);

            $response = $this->actingAs($user, 'sanctum')
                ->getJson('/api/dashboard');

            $response->assertOk();
            $response->assertJsonPath('role', $role);
        }
    }

    public function test_only_admin_can_manage_users_and_roles_are_model_validated(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $teacher = User::factory()->create(['role' => Role::TEACHER]);

        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/users')
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure(['users', 'roles']);

        $this->expectException(InvalidArgumentException::class);
        User::factory()->create(['role' => 'invalid-role']);
    }

    public function test_admin_can_import_multiple_marksheets_and_export_them(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $first = Student::create(['name' => 'First Student', 'email' => 'first@example.com']);
        $second = Student::create(['name' => 'Second Student', 'email' => 'second@example.com']);
        $file = $this->spreadsheetUpload([
            ['student_email', 'subject_name', 'full_marks', 'pass_marks', 'marks'],
            ['first@example.com', 'Math', 100, 40, 80],
            ['second@example.com', 'Science', 100, 40, 70],
        ]);

        $this->actingAs($admin, 'sanctum')
            ->post('/api/marksheets/import', ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('count', 2);

        $this->assertDatabaseCount('marksheets', 2);
        $this->actingAs($admin, 'sanctum')
            ->get('/api/marksheets/export')
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($admin, 'sanctum')
            ->get("/api/marksheets/{$first->marksheets()->first()->id}/export")
            ->assertOk();

        $duplicate = $this->spreadsheetUpload([
            ['student_email', 'subject_name', 'full_marks', 'pass_marks', 'marks'],
            ['first@example.com', 'English', 100, 40, 75],
        ]);

        $this->actingAs($admin, 'sanctum')
            ->post('/api/marksheets/import', ['file' => $duplicate])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Import rejected. No marksheets were saved.');
    }

    public function test_admin_can_assign_and_clear_a_student_parent(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $teacher = User::factory()->create(['role' => Role::TEACHER]);
        $student = Student::create(['name' => 'Unassigned Student', 'email' => 'unassigned@example.com']);
        $parent = StudentParent::create([
            'name' => 'Parent User',
            'email' => 'parent@example.com',
            'phone' => '9800000000',
            'relationship' => 'Guardian',
        ]);

        $this->actingAs($teacher, 'sanctum')
            ->putJson("/api/students/{$student->id}/parent", ['parent_id' => $parent->id])
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/students/{$student->id}/parent", ['parent_id' => $parent->id])
            ->assertOk()
            ->assertJsonPath('student.parent_id', $parent->id);

        $this->assertDatabaseHas('students', ['id' => $student->id, 'parent_id' => $parent->id]);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/students/{$student->id}/parent", ['parent_id' => null])
            ->assertOk()
            ->assertJsonPath('student.parent_id', null);
    }

    public function test_admin_can_assign_or_clear_parent_during_bulk_edit(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $parent = StudentParent::create([
            'name' => 'Bulk Parent',
            'email' => 'bulk-parent@example.com',
            'phone' => '9800000001',
            'relationship' => 'Guardian',
        ]);
        $students = collect([
            Student::create(['name' => 'Bulk One', 'email' => 'bulk-one@example.com']),
            Student::create(['name' => 'Bulk Two', 'email' => 'bulk-two@example.com']),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/students/bulk-update', [
                'ids' => $students->pluck('id')->all(),
                'parent_id' => $parent->id,
            ])
            ->assertOk();

        $this->assertDatabaseCount('students', 2);
        $this->assertDatabaseHas('students', ['id' => $students[0]->id, 'parent_id' => $parent->id]);

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/students/bulk-update', [
                'ids' => $students->pluck('id')->all(),
                'parent_id' => null,
            ])
            ->assertOk();

        $this->assertDatabaseHas('students', ['id' => $students[0]->id, 'parent_id' => null]);
    }

    private function spreadsheetUpload(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows);
        $path = tempnam(sys_get_temp_dir(), 'marksheet-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'marksheets.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
