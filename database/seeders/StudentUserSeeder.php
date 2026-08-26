<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    Student::updateOrCreate(
        ['email' => 'ram@example.com'],
        [
            'name' => 'Ram Sharma',
            'phone' => '9800000001',
            'status' => 'active',
            'photo' => null,
        ]
    );

    Student::updateOrCreate(
        ['email' => 'sita@example.com'],
        [
            'name' => 'Sita Thapa',
            'phone' => '9800000002',
            'status' => 'active',
            'photo' => null,
        ]
    );

    Student::updateOrCreate(
        ['email' => 'hari@example.com'],
        [
            'name' => 'Hari Bahadur',
            'phone' => '9800000003',
            'status' => 'inactive',
            'photo' => null,
        ]
    );

    Student::updateOrCreate(
        ['email' => 'gita@example.com'],
        [
            'name' => 'Gita Karki',
            'phone' => '9800000004',
            'status' => 'active',
            'photo' => null,
        ]
    );
}
}