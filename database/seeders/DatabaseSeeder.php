<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Major;
use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\AttendanceSession;
use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a realistic academic structure.
     */
    public function run(): void
    {
        // 1. Administrative Setup
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'System Admin', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('demo123'),
                'role' => 'admin',
                'is_approved' => true,
            ]
        );

        // 2. Academic Map (30 Subjects Total)
        $academicMap = [
            'Technology' => [
                'Computer Science' => ['C Programming', 'Web Development', 'Java Networking', 'Database Systems', 'Mobile App Dev'],
                'Cybersecurity'   => ['Network Security', 'Ethical Hacking', 'Cryptography', 'Digital Forensics', 'Security Compliance'],
                'Data Science'    => ['Python Statistics', 'Machine Learning', 'Big Data Architecture', 'Data Visualization', 'Deep Learning'],
            ],
            'Business' => [
                'Accounting'       => ['Financial Accounting', 'Corporate Finance', 'Taxation Law', 'Auditing Basics', 'Management Accounting'],
                'Digital Marketing' => ['SEO Strategies', 'Content Strategy', 'Social Media Analytics', 'E-commerce Management', 'PPC Advertising'],
                'Global Trade'      => ['Supply Chain Logistics', 'International Law', 'Import/Export Ops', 'Trade Economics', 'Foreign Exchange'],
            ],
            'Engineering' => [
                'Mechanical' => ['Thermodynamics', 'Fluid Mechanics', 'Engineering Mechatronics', 'CAD/CAM Design', 'Material Science'],
                'Electrical' => ['Circuit Analysis', 'Digital Signal Processing', 'Power Systems', 'Microcontrollers', 'Control Engineering'],
                'Civil'      => ['Structural Analysis', 'Geotechnical Engineering', 'Construction Tech', 'Hydrology', 'Surveying Basics'],
            ],
        ];

        // 3. Execution (Department -> Major -> Group -> Subjects -> Sessions)
        foreach ($academicMap as $deptName => $majors) {
            // Find or Create Department
            $dept = Department::firstOrCreate(
                ['name' => $deptName],
                ['code' => strtoupper(substr($deptName, 0, 3)), 'description' => $deptName . ' Faculty']
            );
            
            // Create a Pool of 3 Teachers for this department
            $teacherPool = collect();
            for ($i = 0; $i < 3; $i++) {
                $teacherName = fake()->name();
                $teacherUser = User::create([
                    'name' => $teacherName,
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'role' => 'teacher'
                ]);
                $teacherPool->push(Teacher::create([
                    'user_id' => $teacherUser->id, 
                    'department_id' => $dept->id,
                    'specialization' => fake()->jobTitle(),
                    'phone' => fake()->phoneNumber(),
                    'status' => 'active'
                ]));
            }

            foreach ($majors as $majorName => $subjectsList) {
                // Create Major
                $major = Major::create([
                    'department_id' => $dept->id, 
                    'name' => $majorName, 
                    'code' => strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $majorName)))) . rand(10, 99)
                ]);
                
                // One Class Group per Major
                $group = ClassGroup::create([
                    'major_id' => $major->id, 
                    'name' => $majorName . ' - Cohort A', 
                    'year_level' => 1
                ]);
                
                // Create 30 Students for this Group manually to avoid Factory Chaining
                $students = collect();
                for ($s = 0; $s < 30; $s++) {
                    $stuName = fake()->name();
                    $stuUser = User::create([
                        'name' => $stuName,
                        'email' => fake()->unique()->safeEmail(),
                        'password' => Hash::make('password'),
                        'role' => 'student'
                    ]);
                    $students->push(Student::create([
                        'user_id' => $stuUser->id,
                        'student_code' => 'STD-' . rand(10000, 99999),
                        'group_id' => $group->id,
                        'major_id' => $major->id,
                        'year_level' => 1,
                        'status' => 'active',
                        'major' => $majorName // Legacy field
                    ]));
                }

                foreach ($subjectsList as $subName) {
                    $subject = Subject::create([
                        'department_id' => $dept->id, 
                        'name' => $subName, 
                        'code' => strtoupper(substr($subName, 0, 3)) . rand(100, 999)
                    ]);
                    
                    // Assign a Teacher from the department pool
                    $teacher = $teacherPool->random();
                    
                    // Course/Session Offering
                    $class = ClassRoom::create([
                        'name' => $subName . ' (' . $group->name . ')',
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'group_id'   => $group->id,
                        'room_number' => 'Lab ' . rand(101, 599),
                        'semester' => 2,
                        'academic_year' => '2025-2026',
                        'status' => 'active',
                        'schedule' => fake()->randomElement(['Mon/Wed/Fri', 'Tue/Thu']) . ' ' . fake()->randomElement(['08:00-10:00', '13:30-15:30'])
                    ]);

                    // Historical Attendance (Last 3 Weeks - 6 Sessions per Subject)
                    $currentDate = now()->subWeeks(3)->startOfDay()->addHours(8);
                    for ($week = 1; $week <= 6; $week++) {
                        $session = AttendanceSession::create([
                            'class_id' => $class->id,
                            'qr_token' => bin2hex(random_bytes(8)),
                            'start_time' => (clone $currentDate),
                            'end_time' => (clone $currentDate)->addHours(3),
                            'checkin_open_time' => (clone $currentDate)->subMinutes(30),
                            'status' => 'completed',
                            'semester' => 2,
                            'academic_year' => '2025-2026',
                        ]);

                        foreach ($students as $stu) {
                            Attendance::create([
                                'student_id' => $stu->id,
                                'session_id' => $session->id,
                                'status' => fake()->boolean(85) ? 'present' : (fake()->boolean(50) ? 'absent' : 'late'),
                                'scan_time' => (clone $currentDate)->addMinutes(rand(1, 45)),
                                'method' => 'qr',
                            ]);
                        }
                        $currentDate->addDays(fake()->randomElement([2, 3]));
                    }
                }
            }
        }
    }
}
