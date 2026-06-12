<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Epic;
use App\Models\Project;
use App\Models\ProjectFavorite;
use App\Models\ProjectStatus;
use App\Models\ProjectUser;
use App\Models\Sprint;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketComment;
use App\Models\TicketHour;
use App\Models\TicketPriority;
use App\Models\TicketRelation;
use App\Models\TicketStatus;
use App\Models\TicketSubscriber;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FullProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Membuat 5 project lengkap dengan semua aspek:
     * - Users (8 users)
     * - Project Statuses
     * - Projects (5 project: 3 scrum, 2 kanban)
     * - Project Users & Favorites
     * - Epics & Sprints
     * - Tickets dengan status, priority, type, assignee
     * - Ticket Comments, Hours, Activities, Relations, Subscribers
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🚀 Memulai Full Project Seeder...');

        // ============================================================
        // 1. USERS
        // ============================================================
        $this->command->info('👤 Membuat users...');
        $users = $this->createUsers();

        // ============================================================
        // 2. PROJECT STATUSES
        // ============================================================
        $this->command->info('📊 Membuat project statuses...');
        $projectStatuses = $this->createProjectStatuses();

        // ============================================================
        // 3. ENSURE REFERENCE DATA EXISTS
        // ============================================================
        $this->command->info('📋 Memverifikasi reference data...');
        $ticketTypes = TicketType::all();
        $ticketPriorities = TicketPriority::all();
        $ticketStatuses = TicketStatus::whereNull('project_id')->get();
        $activities = Activity::all();

        if ($ticketTypes->isEmpty() || $ticketPriorities->isEmpty() || $ticketStatuses->isEmpty() || $activities->isEmpty()) {
            $this->command->warn('⚠️  Reference data belum lengkap. Jalankan DatabaseSeeder terlebih dahulu.');
            return;
        }

        // ============================================================
        // 4. PROJECTS
        // ============================================================
        $this->command->info('📁 Membuat 5 projects...');
        $projects = $this->createProjects($users, $projectStatuses);

        // ============================================================
        // 5. PROJECT USERS & FAVORITES
        // ============================================================
        $this->command->info('👥 Menambahkan project members & favorites...');
        $this->assignProjectUsers($projects, $users);

        // ============================================================
        // 6. PROJECT-SPECIFIC TICKET STATUSES
        // ============================================================
        $this->command->info('🏷️  Membuat ticket statuses per project...');
        $this->createProjectTicketStatuses($projects);

        // ============================================================
        // 7. EPICS
        // ============================================================
        $this->command->info('🎯 Membuat epics...');
        $epics = $this->createEpics($projects);

        // ============================================================
        // 8. SPRINTS
        // ============================================================
        $this->command->info('🏃 Membuat sprints...');
        $sprints = $this->createSprints($projects, $epics);

        // ============================================================
        // 9. TICKETS
        // ============================================================
        $this->command->info('🎫 Membuat tickets...');
        $tickets = $this->createTickets($projects, $users, $ticketTypes, $ticketPriorities, $ticketStatuses, $epics, $sprints);

        // ============================================================
        // 10. TICKET COMMENTS
        // ============================================================
        $this->command->info('💬 Membuat ticket comments...');
        $this->createTicketComments($tickets, $users);

        // ============================================================
        // 11. TICKET HOURS (TIME TRACKING)
        // ============================================================
        $this->command->info('⏱️  Membuat ticket hours...');
        $this->createTicketHours($tickets, $users, $activities);

        // ============================================================
        // 12. TICKET ACTIVITIES (STATUS CHANGES)
        // ============================================================
        $this->command->info('📝 Membuat ticket activities...');
        $this->createTicketActivities($tickets, $users, $ticketStatuses);

        // ============================================================
        // 13. TICKET RELATIONS
        // ============================================================
        $this->command->info('🔗 Membuat ticket relations...');
        $this->createTicketRelations($tickets);

        // ============================================================
        // 14. TICKET SUBSCRIBERS
        // ============================================================
        $this->command->info('🔔 Membuat ticket subscribers...');
        $this->createTicketSubscribers($tickets, $users);

        $this->command->info('✅ Full Project Seeder selesai!');
        $this->command->info("   📁 {$projects->count()} Projects");
        $this->command->info("   👤 " . count($users) . " Users");
        $this->command->info("   🎯 " . Epic::count() . " Epics");
        $this->command->info("   🏃 " . Sprint::count() . " Sprints");
        $this->command->info("   🎫 " . Ticket::count() . " Tickets");
        $this->command->info("   💬 " . TicketComment::count() . " Comments");
        $this->command->info("   ⏱️  " . TicketHour::count() . " Time Logs");
        $this->command->info("   📝 " . TicketActivity::count() . " Activities");
        $this->command->info("   🔗 " . TicketRelation::count() . " Relations");
        $this->command->info("   🔔 " . TicketSubscriber::count() . " Subscribers");
    }

    // ================================================================
    // USER CREATION
    // ================================================================
    private function createUsers(): array
    {
        // Get existing default user (John DOE) first - this is the user
        // that's used to login, so they MUST be connected to projects
        $defaultUser = User::where('email', 'john.doe@helper.app')->first();
        if (!$defaultUser) {
            $defaultUser = User::first();
        }

        $users = [];
        if ($defaultUser) {
            $users[] = $defaultUser; // $users[0] = John DOE (default login user)
        }

        $usersData = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@company.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subMonths(6),
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@company.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subMonths(6),
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@company.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now()->subMonths(5),
                'updated_at' => now()->subMonths(5),
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@company.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now()->subMonths(5),
                'updated_at' => now()->subMonths(5),
            ],
            [
                'name' => 'Reza Pratama',
                'email' => 'reza.pratama@company.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subMonths(4),
            ],
            [
                'name' => 'Maya Angelina',
                'email' => 'maya.angelina@company.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subMonths(4),
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi.wijaya@company.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ],
            [
                'name' => 'Rina Fitriani',
                'email' => 'rina.fitriani@company.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ],
        ];

        foreach ($usersData as $data) {
            // Use DB::table to avoid model boot events (notification sending)
            $existing = DB::table('users')->where('email', $data['email'])->first();
            if ($existing) {
                $users[] = User::find($existing->id);
                continue;
            }
            $id = DB::table('users')->insertGetId($data);
            $user = User::find($id);
            // Assign default role if it exists
            $defaultRole = DB::table('roles')->where('name', 'Default role')->first();
            if ($defaultRole) {
                $user->syncRoles(['Default role']);
            }
            $users[] = $user;
        }

        return $users;
    }

    // ================================================================
    // PROJECT STATUSES
    // ================================================================
    private function createProjectStatuses()
    {
        $statuses = [
            ['name' => 'Active', 'color' => '#28a745', 'is_default' => true],
            ['name' => 'On Hold', 'color' => '#ffc107', 'is_default' => false],
            ['name' => 'Completed', 'color' => '#007bff', 'is_default' => false],
            ['name' => 'Cancelled', 'color' => '#dc3545', 'is_default' => false],
        ];

        $created = [];
        foreach ($statuses as $status) {
            $created[] = ProjectStatus::firstOrCreate(
                ['name' => $status['name']],
                $status
            );
        }

        return collect($created);
    }

    // ================================================================
    // PROJECTS
    // ================================================================
    private function createProjects($users, $projectStatuses)
    {
        $activeStatus = $projectStatuses->firstWhere('name', 'Active');
        $onHoldStatus = $projectStatuses->firstWhere('name', 'On Hold');
        $completedStatus = $projectStatuses->firstWhere('name', 'Completed');

        $projectsData = [
            // Project 1: E-Commerce Platform (Scrum)
            [
                'name' => 'E-Commerce Platform',
                'description' => 'Platform e-commerce modern dengan fitur marketplace, payment gateway integration, dan real-time inventory management. Dibangun menggunakan microservices architecture untuk scalability tinggi.',
                'status_id' => $activeStatus->id,
                'owner_id' => $users[0]->id, // John DOE owns this project
                'ticket_prefix' => 'ECOM',
                'status_type' => 'custom',
                'type' => 'scrum',
                'created_at' => now()->subMonths(5),
                'updated_at' => now()->subDays(1),
            ],
            // Project 2: Mobile Banking App (Scrum)
            [
                'name' => 'Mobile Banking App',
                'description' => 'Aplikasi mobile banking dengan fitur transfer, pembayaran, investasi, dan financial planning. Fokus pada keamanan tinggi dengan biometric authentication dan end-to-end encryption.',
                'status_id' => $activeStatus->id,
                'owner_id' => $users[2]->id,
                'ticket_prefix' => 'MBANK',
                'status_type' => 'custom',
                'type' => 'scrum',
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subDays(2),
            ],
            // Project 3: HR Management System (Kanban)
            [
                'name' => 'HR Management System',
                'description' => 'Sistem manajemen HR yang mencakup employee management, payroll processing, leave management, performance tracking, dan recruitment pipeline.',
                'status_id' => $activeStatus->id,
                'owner_id' => $users[0]->id, // John DOE owns this project
                'ticket_prefix' => 'HRMS',
                'status_type' => 'default',
                'type' => 'kanban',
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subDays(3),
            ],
            // Project 4: Learning Management System (Scrum)
            [
                'name' => 'Learning Management System',
                'description' => 'Platform e-learning dengan fitur video streaming, quiz engine, progress tracking, certification, dan interactive learning paths untuk corporate training.',
                'status_id' => $onHoldStatus->id,
                'owner_id' => $users[4]->id,
                'ticket_prefix' => 'LMS',
                'status_type' => 'default',
                'type' => 'scrum',
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subWeeks(2),
            ],
            // Project 5: IoT Dashboard (Kanban)
            [
                'name' => 'IoT Dashboard & Monitoring',
                'description' => 'Dashboard monitoring untuk IoT devices dengan real-time data visualization, alerting system, device management, dan predictive maintenance analytics.',
                'status_id' => $completedStatus->id,
                'owner_id' => $users[5]->id,
                'ticket_prefix' => 'IOT',
                'status_type' => 'custom',
                'type' => 'kanban',
                'created_at' => now()->subMonths(8),
                'updated_at' => now()->subMonths(1),
            ],
        ];

        $projects = collect();
        foreach ($projectsData as $data) {
            $project = Project::create($data);
            $projects->push($project);
        }

        return $projects;
    }

    // ================================================================
    // PROJECT USERS & FAVORITES
    // ================================================================
    private function assignProjectUsers($projects, $users)
    {
        // Project 1: E-Commerce - owned by John DOE ($users[0]), add team members
        $this->addProjectMembers($projects[0], [
            ['user_id' => $users[1]->id, 'role' => 'administrator'],
            ['user_id' => $users[2]->id, 'role' => 'employee'],
            ['user_id' => $users[3]->id, 'role' => 'employee'],
            ['user_id' => $users[4]->id, 'role' => 'employee'],
            ['user_id' => $users[5]->id, 'role' => 'employee'],
            ['user_id' => $users[7]->id, 'role' => 'customer'],
        ]);

        // Project 2: Mobile Banking - add John DOE as administrator
        $this->addProjectMembers($projects[1], [
            ['user_id' => $users[0]->id, 'role' => 'administrator'],
            ['user_id' => $users[1]->id, 'role' => 'employee'],
            ['user_id' => $users[4]->id, 'role' => 'administrator'],
            ['user_id' => $users[6]->id, 'role' => 'employee'],
            ['user_id' => $users[8]->id, 'role' => 'employee'],
        ]);

        // Project 3: HR Management - owned by John DOE ($users[0]), add team members
        $this->addProjectMembers($projects[2], [
            ['user_id' => $users[1]->id, 'role' => 'administrator'],
            ['user_id' => $users[3]->id, 'role' => 'employee'],
            ['user_id' => $users[5]->id, 'role' => 'employee'],
            ['user_id' => $users[6]->id, 'role' => 'employee'],
        ]);

        // Project 4: LMS - add John DOE as member
        $this->addProjectMembers($projects[3], [
            ['user_id' => $users[0]->id, 'role' => 'administrator'],
            ['user_id' => $users[2]->id, 'role' => 'employee'],
            ['user_id' => $users[3]->id, 'role' => 'administrator'],
            ['user_id' => $users[7]->id, 'role' => 'employee'],
            ['user_id' => $users[8]->id, 'role' => 'employee'],
        ]);

        // Project 5: IoT Dashboard - add John DOE as member
        $this->addProjectMembers($projects[4], [
            ['user_id' => $users[0]->id, 'role' => 'employee'],
            ['user_id' => $users[1]->id, 'role' => 'employee'],
            ['user_id' => $users[3]->id, 'role' => 'employee'],
            ['user_id' => $users[7]->id, 'role' => 'administrator'],
        ]);

        // Add favorites - John DOE favorites all active projects
        $favoritePairs = [
            [$users[0]->id, $projects[0]->id],
            [$users[0]->id, $projects[1]->id],
            [$users[0]->id, $projects[2]->id],
            [$users[1]->id, $projects[0]->id],
            [$users[2]->id, $projects[1]->id],
            [$users[3]->id, $projects[0]->id],
            [$users[4]->id, $projects[3]->id],
            [$users[5]->id, $projects[4]->id],
            [$users[6]->id, $projects[1]->id],
            [$users[7]->id, $projects[3]->id],
            [$users[8]->id, $projects[1]->id],
        ];

        foreach ($favoritePairs as [$userId, $projectId]) {
            ProjectFavorite::firstOrCreate([
                'user_id' => $userId,
                'project_id' => $projectId,
            ]);
        }
    }

    private function addProjectMembers($project, $members)
    {
        foreach ($members as $member) {
            ProjectUser::firstOrCreate(
                ['user_id' => $member['user_id'], 'project_id' => $project->id],
                $member + ['project_id' => $project->id]
            );
        }
    }

    // ================================================================
    // PROJECT-SPECIFIC TICKET STATUSES
    // ================================================================
    private function createProjectTicketStatuses($projects)
    {
        // Custom statuses for Project 1 (E-Commerce)
        $ecomStatuses = [
            ['name' => 'Backlog', 'color' => '#6c757d', 'is_default' => true, 'order' => 1, 'project_id' => $projects[0]->id],
            ['name' => 'Ready for Dev', 'color' => '#17a2b8', 'is_default' => false, 'order' => 2, 'project_id' => $projects[0]->id],
            ['name' => 'In Development', 'color' => '#ff7f00', 'is_default' => false, 'order' => 3, 'project_id' => $projects[0]->id],
            ['name' => 'Code Review', 'color' => '#6f42c1', 'is_default' => false, 'order' => 4, 'project_id' => $projects[0]->id],
            ['name' => 'QA Testing', 'color' => '#e83e8c', 'is_default' => false, 'order' => 5, 'project_id' => $projects[0]->id],
            ['name' => 'Done', 'color' => '#28a745', 'is_default' => false, 'order' => 6, 'project_id' => $projects[0]->id],
        ];

        foreach ($ecomStatuses as $status) {
            DB::table('ticket_statuses')->insert($status + ['created_at' => now(), 'updated_at' => now()]);
        }

        // Custom statuses for Project 2 (Mobile Banking)
        $bankStatuses = [
            ['name' => 'New', 'color' => '#cecece', 'is_default' => true, 'order' => 1, 'project_id' => $projects[1]->id],
            ['name' => 'Analysis', 'color' => '#17a2b8', 'is_default' => false, 'order' => 2, 'project_id' => $projects[1]->id],
            ['name' => 'Development', 'color' => '#ff7f00', 'is_default' => false, 'order' => 3, 'project_id' => $projects[1]->id],
            ['name' => 'Security Review', 'color' => '#dc3545', 'is_default' => false, 'order' => 4, 'project_id' => $projects[1]->id],
            ['name' => 'UAT', 'color' => '#6f42c1', 'is_default' => false, 'order' => 5, 'project_id' => $projects[1]->id],
            ['name' => 'Released', 'color' => '#28a745', 'is_default' => false, 'order' => 6, 'project_id' => $projects[1]->id],
        ];

        foreach ($bankStatuses as $status) {
            DB::table('ticket_statuses')->insert($status + ['created_at' => now(), 'updated_at' => now()]);
        }

        // Custom statuses for Project 5 (IoT)
        $iotStatuses = [
            ['name' => 'Incoming', 'color' => '#cecece', 'is_default' => true, 'order' => 1, 'project_id' => $projects[4]->id],
            ['name' => 'Investigating', 'color' => '#ffc107', 'is_default' => false, 'order' => 2, 'project_id' => $projects[4]->id],
            ['name' => 'Implementing', 'color' => '#ff7f00', 'is_default' => false, 'order' => 3, 'project_id' => $projects[4]->id],
            ['name' => 'Deployed', 'color' => '#28a745', 'is_default' => false, 'order' => 4, 'project_id' => $projects[4]->id],
        ];

        foreach ($iotStatuses as $status) {
            DB::table('ticket_statuses')->insert($status + ['created_at' => now(), 'updated_at' => now()]);
        }
    }

    // ================================================================
    // EPICS
    // ================================================================
    private function createEpics($projects)
    {
        $epicsData = [
            // Project 1: E-Commerce
            ['name' => 'User Authentication & Authorization', 'project_id' => $projects[0]->id, 'starts_at' => now()->subMonths(5), 'ends_at' => now()->subMonths(4)],
            ['name' => 'Product Catalog & Search', 'project_id' => $projects[0]->id, 'starts_at' => now()->subMonths(4), 'ends_at' => now()->subMonths(3)],
            ['name' => 'Shopping Cart & Checkout', 'project_id' => $projects[0]->id, 'starts_at' => now()->subMonths(3), 'ends_at' => now()->subMonths(2)],
            ['name' => 'Payment Gateway Integration', 'project_id' => $projects[0]->id, 'starts_at' => now()->subMonths(2), 'ends_at' => now()->subMonth()],
            ['name' => 'Order Management & Tracking', 'project_id' => $projects[0]->id, 'starts_at' => now()->subMonth(), 'ends_at' => now()->addMonth()],

            // Project 2: Mobile Banking
            ['name' => 'Biometric Authentication', 'project_id' => $projects[1]->id, 'starts_at' => now()->subMonths(4), 'ends_at' => now()->subMonths(3)],
            ['name' => 'Fund Transfer Module', 'project_id' => $projects[1]->id, 'starts_at' => now()->subMonths(3), 'ends_at' => now()->subMonths(2)],
            ['name' => 'Bill Payment System', 'project_id' => $projects[1]->id, 'starts_at' => now()->subMonths(2), 'ends_at' => now()->subMonth()],
            ['name' => 'Investment & Portfolio', 'project_id' => $projects[1]->id, 'starts_at' => now()->subMonth(), 'ends_at' => now()->addMonth()],

            // Project 3: HR Management
            ['name' => 'Employee Database', 'project_id' => $projects[2]->id, 'starts_at' => now()->subMonths(3), 'ends_at' => now()->subMonths(2)],
            ['name' => 'Payroll Processing', 'project_id' => $projects[2]->id, 'starts_at' => now()->subMonths(2), 'ends_at' => now()->subMonth()],
            ['name' => 'Leave Management', 'project_id' => $projects[2]->id, 'starts_at' => now()->subMonth(), 'ends_at' => now()->addMonth()],

            // Project 4: LMS
            ['name' => 'Course Builder', 'project_id' => $projects[3]->id, 'starts_at' => now()->subMonths(6), 'ends_at' => now()->subMonths(5)],
            ['name' => 'Video Streaming Engine', 'project_id' => $projects[3]->id, 'starts_at' => now()->subMonths(5), 'ends_at' => now()->subMonths(4)],
            ['name' => 'Quiz & Assessment', 'project_id' => $projects[3]->id, 'starts_at' => now()->subMonths(4), 'ends_at' => now()->subMonths(3)],
            ['name' => 'Certification System', 'project_id' => $projects[3]->id, 'starts_at' => now()->subMonths(3), 'ends_at' => now()->subMonth()],

            // Project 5: IoT Dashboard
            ['name' => 'Device Registration & Management', 'project_id' => $projects[4]->id, 'starts_at' => now()->subMonths(8), 'ends_at' => now()->subMonths(7)],
            ['name' => 'Real-time Data Pipeline', 'project_id' => $projects[4]->id, 'starts_at' => now()->subMonths(7), 'ends_at' => now()->subMonths(5)],
            ['name' => 'Dashboard Visualization', 'project_id' => $projects[4]->id, 'starts_at' => now()->subMonths(5), 'ends_at' => now()->subMonths(3)],
            ['name' => 'Alert & Notification System', 'project_id' => $projects[4]->id, 'starts_at' => now()->subMonths(3), 'ends_at' => now()->subMonths(1)],
        ];

        $epics = collect();
        foreach ($epicsData as $data) {
            $epic = Epic::create($data);
            $epics->push($epic);
        }

        return $epics;
    }

    // ================================================================
    // SPRINTS
    // ================================================================
    private function createSprints($projects, $epics)
    {
        // We insert sprints directly via DB to avoid the boot event
        // that auto-creates epics. We already have epics created above.
        $sprintsData = [
            // Project 1: E-Commerce (3 sprints)
            [
                'name' => 'Sprint 1 - Auth & Setup',
                'starts_at' => now()->subMonths(5)->toDateString(),
                'ends_at' => now()->subMonths(5)->addWeeks(2)->toDateString(),
                'description' => 'Setup proyek dasar, implementasi authentication, user registration, dan role management.',
                'project_id' => $projects[0]->id,
                'epic_id' => $epics->where('project_id', $projects[0]->id)->values()[0]->id,
                'started_at' => now()->subMonths(5),
                'ended_at' => now()->subMonths(5)->addWeeks(2),
                'created_at' => now()->subMonths(5),
                'updated_at' => now()->subMonths(5)->addWeeks(2),
            ],
            [
                'name' => 'Sprint 2 - Product Catalog',
                'starts_at' => now()->subMonths(4)->toDateString(),
                'ends_at' => now()->subMonths(4)->addWeeks(2)->toDateString(),
                'description' => 'Implementasi product listing, search engine, filtering, dan category management.',
                'project_id' => $projects[0]->id,
                'epic_id' => $epics->where('project_id', $projects[0]->id)->values()[1]->id,
                'started_at' => now()->subMonths(4),
                'ended_at' => now()->subMonths(4)->addWeeks(2),
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subMonths(4)->addWeeks(2),
            ],
            [
                'name' => 'Sprint 3 - Cart & Checkout',
                'starts_at' => now()->subMonths(2)->toDateString(),
                'ends_at' => now()->subMonths(2)->addWeeks(2)->toDateString(),
                'description' => 'Implementasi shopping cart, checkout flow, dan address management.',
                'project_id' => $projects[0]->id,
                'epic_id' => $epics->where('project_id', $projects[0]->id)->values()[2]->id,
                'started_at' => now()->subMonths(2),
                'ended_at' => null,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subDays(1),
            ],

            // Project 2: Mobile Banking (3 sprints)
            [
                'name' => 'Sprint 1 - Biometric Auth',
                'starts_at' => now()->subMonths(4)->toDateString(),
                'ends_at' => now()->subMonths(4)->addWeeks(2)->toDateString(),
                'description' => 'Implementasi fingerprint dan face recognition authentication.',
                'project_id' => $projects[1]->id,
                'epic_id' => $epics->where('project_id', $projects[1]->id)->values()[0]->id,
                'started_at' => now()->subMonths(4),
                'ended_at' => now()->subMonths(4)->addWeeks(2),
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subMonths(4)->addWeeks(2),
            ],
            [
                'name' => 'Sprint 2 - Transfer Module',
                'starts_at' => now()->subMonths(3)->toDateString(),
                'ends_at' => now()->subMonths(3)->addWeeks(2)->toDateString(),
                'description' => 'Implementasi intra-bank transfer, inter-bank transfer, dan scheduled transfer.',
                'project_id' => $projects[1]->id,
                'epic_id' => $epics->where('project_id', $projects[1]->id)->values()[1]->id,
                'started_at' => now()->subMonths(3),
                'ended_at' => now()->subMonths(3)->addWeeks(2),
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3)->addWeeks(2),
            ],
            [
                'name' => 'Sprint 3 - Bill Payment',
                'starts_at' => now()->subMonth()->toDateString(),
                'ends_at' => now()->subMonth()->addWeeks(2)->toDateString(),
                'description' => 'Implementasi pembayaran listrik, air, internet, dan biller management.',
                'project_id' => $projects[1]->id,
                'epic_id' => $epics->where('project_id', $projects[1]->id)->values()[2]->id,
                'started_at' => now()->subMonth(),
                'ended_at' => null,
                'created_at' => now()->subMonth(),
                'updated_at' => now()->subDays(3),
            ],

            // Project 4: LMS (2 sprints)
            [
                'name' => 'Sprint 1 - Course Builder',
                'starts_at' => now()->subMonths(6)->toDateString(),
                'ends_at' => now()->subMonths(6)->addWeeks(2)->toDateString(),
                'description' => 'Implementasi course creation wizard, module management, dan content upload.',
                'project_id' => $projects[3]->id,
                'epic_id' => $epics->where('project_id', $projects[3]->id)->values()[0]->id,
                'started_at' => now()->subMonths(6),
                'ended_at' => now()->subMonths(6)->addWeeks(2),
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subMonths(6)->addWeeks(2),
            ],
            [
                'name' => 'Sprint 2 - Video Engine',
                'starts_at' => now()->subMonths(5)->toDateString(),
                'ends_at' => now()->subMonths(5)->addWeeks(2)->toDateString(),
                'description' => 'Implementasi video upload, transcoding, adaptive streaming, dan video player.',
                'project_id' => $projects[3]->id,
                'epic_id' => $epics->where('project_id', $projects[3]->id)->values()[1]->id,
                'started_at' => now()->subMonths(5),
                'ended_at' => now()->subMonths(5)->addWeeks(2),
                'created_at' => now()->subMonths(5),
                'updated_at' => now()->subMonths(5)->addWeeks(2),
            ],
        ];

        $sprints = collect();
        foreach ($sprintsData as $data) {
            $id = DB::table('sprints')->insertGetId($data);
            $sprints->push(Sprint::find($id));
        }

        return $sprints;
    }

    // ================================================================
    // TICKETS
    // ================================================================
    private function createTickets($projects, $users, $ticketTypes, $ticketPriorities, $ticketStatuses, $epics, $sprints)
    {
        $typeTask = $ticketTypes->firstWhere('name', 'Task');
        $typeBug = $ticketTypes->firstWhere('name', 'Bug');
        $typeEvolution = $ticketTypes->firstWhere('name', 'Evolution');

        $priorityLow = $ticketPriorities->firstWhere('name', 'Low');
        $priorityNormal = $ticketPriorities->firstWhere('name', 'Normal');
        $priorityHigh = $ticketPriorities->firstWhere('name', 'High');

        // Helper to get project-specific statuses or global ones
        $getStatuses = function ($project) {
            $statuses = TicketStatus::where('project_id', $project->id)->get();
            if ($statuses->isEmpty()) {
                $statuses = TicketStatus::whereNull('project_id')->get();
            }
            return $statuses;
        };

        $allTickets = collect();
        $ticketCounter = [];

        // ---- PROJECT 1: E-Commerce (12 tickets) ----
        $p1Statuses = $getStatuses($projects[0]);
        $p1Epics = $epics->where('project_id', $projects[0]->id)->values();
        $p1Sprints = $sprints->where('project_id', $projects[0]->id)->values();

        $p1Tickets = [
            ['name' => 'Setup Laravel project dengan Docker', 'content' => 'Konfigurasi Docker compose untuk development environment termasuk PHP, MySQL, Redis, dan Nginx. Setup CI/CD pipeline dengan GitHub Actions.', 'owner_id' => $users[0]->id, 'responsible_id' => $users[2]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 8, 'epic_id' => $p1Epics[0]->id, 'sprint_id' => $p1Sprints[0]->id, 'status_index' => 5],
            ['name' => 'Implementasi JWT Authentication', 'content' => 'Implementasi JSON Web Token authentication dengan refresh token rotation, blacklisting, dan rate limiting. Termasuk login, register, forgot password, dan email verification.', 'owner_id' => $users[0]->id, 'responsible_id' => $users[1]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 16, 'epic_id' => $p1Epics[0]->id, 'sprint_id' => $p1Sprints[0]->id, 'status_index' => 5],
            ['name' => 'Role-based Access Control (RBAC)', 'content' => 'Implementasi sistem RBAC dengan roles: admin, seller, buyer. Termasuk permission management dan middleware guard.', 'owner_id' => $users[1]->id, 'responsible_id' => $users[3]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityNormal->id, 'estimation' => 12, 'epic_id' => $p1Epics[0]->id, 'sprint_id' => $p1Sprints[0]->id, 'status_index' => 5],
            ['name' => 'Product CRUD dengan Image Upload', 'content' => 'Buat fitur CRUD product dengan multiple image upload, image optimization, dan lazy loading. Termasuk product variants (size, color).', 'owner_id' => $users[0]->id, 'responsible_id' => $users[2]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 20, 'epic_id' => $p1Epics[1]->id, 'sprint_id' => $p1Sprints[1]->id, 'status_index' => 5],
            ['name' => 'Elasticsearch Integration untuk Search', 'content' => 'Integrasi Elasticsearch untuk full-text search product dengan autocomplete, fuzzy matching, dan faceted filtering.', 'owner_id' => $users[2]->id, 'responsible_id' => $users[4]->id, 'type_id' => $typeEvolution->id, 'priority_id' => $priorityNormal->id, 'estimation' => 24, 'epic_id' => $p1Epics[1]->id, 'sprint_id' => $p1Sprints[1]->id, 'status_index' => 4],
            ['name' => 'Bug: Product images tidak muncul di mobile', 'content' => 'Product images tidak responsive di mobile view. Image container overflow dan aspect ratio tidak terjaga. Perlu fix CSS dan image optimization.', 'owner_id' => $users[3]->id, 'responsible_id' => $users[2]->id, 'type_id' => $typeBug->id, 'priority_id' => $priorityHigh->id, 'estimation' => 4, 'epic_id' => $p1Epics[1]->id, 'sprint_id' => null, 'status_index' => 2],
            ['name' => 'Shopping Cart dengan Redis Cache', 'content' => 'Implementasi shopping cart menggunakan Redis untuk performance. Support guest cart merge saat login, cart persistence, dan cart expiry.', 'owner_id' => $users[0]->id, 'responsible_id' => $users[4]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 16, 'epic_id' => $p1Epics[2]->id, 'sprint_id' => $p1Sprints[2]->id, 'status_index' => 3],
            ['name' => 'Checkout Flow Multi-step', 'content' => 'Buat checkout flow multi-step: 1) Review cart, 2) Shipping address, 3) Payment method, 4) Order confirmation. Termasuk address validation dan shipping cost calculation.', 'owner_id' => $users[1]->id, 'responsible_id' => $users[3]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityNormal->id, 'estimation' => 20, 'epic_id' => $p1Epics[2]->id, 'sprint_id' => $p1Sprints[2]->id, 'status_index' => 2],
            ['name' => 'Midtrans Payment Gateway', 'content' => 'Integrasi Midtrans untuk pembayaran via credit card, bank transfer, e-wallet (GoPay, OVO, Dana). Implementasi webhook untuk payment notification.', 'owner_id' => $users[0]->id, 'responsible_id' => $users[1]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 24, 'epic_id' => $p1Epics[3]->id, 'sprint_id' => null, 'status_index' => 1],
            ['name' => 'Order Tracking dengan Real-time Update', 'content' => 'Implementasi order tracking dengan status update real-time menggunakan WebSocket. Integrasi dengan logistics API untuk tracking pengiriman.', 'owner_id' => $users[2]->id, 'responsible_id' => $users[4]->id, 'type_id' => $typeEvolution->id, 'priority_id' => $priorityNormal->id, 'estimation' => 16, 'epic_id' => $p1Epics[4]->id, 'sprint_id' => null, 'status_index' => 0],
            ['name' => 'Bug: Race condition pada stock update', 'content' => 'Saat concurrent purchase, stock product bisa menjadi negatif. Perlu implementasi database locking atau optimistic concurrency control.', 'owner_id' => $users[4]->id, 'responsible_id' => $users[0]->id, 'type_id' => $typeBug->id, 'priority_id' => $priorityHigh->id, 'estimation' => 8, 'epic_id' => null, 'sprint_id' => null, 'status_index' => 1],
            ['name' => 'Email notification untuk order status', 'content' => 'Kirim email notification ke buyer saat order status berubah: payment confirmed, shipped, delivered. Gunakan queue untuk async processing.', 'owner_id' => $users[1]->id, 'responsible_id' => $users[3]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityLow->id, 'estimation' => 6, 'epic_id' => $p1Epics[4]->id, 'sprint_id' => null, 'status_index' => 0],
        ];

        $allTickets = $allTickets->merge($this->insertTickets($projects[0], $p1Tickets, $p1Statuses));

        // ---- PROJECT 2: Mobile Banking (10 tickets) ----
        $p2Statuses = $getStatuses($projects[1]);
        $p2Epics = $epics->where('project_id', $projects[1]->id)->values();
        $p2Sprints = $sprints->where('project_id', $projects[1]->id)->values();

        $p2Tickets = [
            ['name' => 'Fingerprint SDK Integration', 'content' => 'Integrasi fingerprint SDK untuk Android (BiometricPrompt) dan iOS (Touch ID/Face ID). Fallback ke PIN jika biometric tidak tersedia.', 'owner_id' => $users[1]->id, 'responsible_id' => $users[5]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 20, 'epic_id' => $p2Epics[0]->id, 'sprint_id' => $p2Sprints[0]->id, 'status_index' => 5],
            ['name' => 'Secure Token Storage', 'content' => 'Implementasi secure storage untuk auth tokens menggunakan Android Keystore dan iOS Keychain. Enkripsi AES-256 untuk data sensitif.', 'owner_id' => $users[1]->id, 'responsible_id' => $users[7]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 16, 'epic_id' => $p2Epics[0]->id, 'sprint_id' => $p2Sprints[0]->id, 'status_index' => 5],
            ['name' => 'Intra-bank Transfer API', 'content' => 'Buat API untuk transfer antar rekening dalam bank yang sama. Termasuk validasi saldo, limit harian, dan transaction logging.', 'owner_id' => $users[3]->id, 'responsible_id' => $users[0]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 24, 'epic_id' => $p2Epics[1]->id, 'sprint_id' => $p2Sprints[1]->id, 'status_index' => 5],
            ['name' => 'BI-FAST Integration', 'content' => 'Integrasi dengan sistem BI-FAST Bank Indonesia untuk real-time inter-bank transfer. Implementasi SNAP API sesuai standar BI.', 'owner_id' => $users[1]->id, 'responsible_id' => $users[5]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 32, 'epic_id' => $p2Epics[1]->id, 'sprint_id' => $p2Sprints[1]->id, 'status_index' => 4],
            ['name' => 'Scheduled Transfer Feature', 'content' => 'Fitur transfer terjadwal: one-time scheduled, recurring (harian/mingguan/bulanan). Implementasi job scheduler dengan retry mechanism.', 'owner_id' => $users[5]->id, 'responsible_id' => $users[7]->id, 'type_id' => $typeEvolution->id, 'priority_id' => $priorityNormal->id, 'estimation' => 16, 'epic_id' => $p2Epics[1]->id, 'sprint_id' => null, 'status_index' => 2],
            ['name' => 'Bill Payment - PLN & PDAM', 'content' => 'Implementasi pembayaran tagihan listrik (PLN) dan air (PDAM). Integrasi dengan biller aggregator untuk inquiry dan payment.', 'owner_id' => $users[3]->id, 'responsible_id' => $users[0]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityNormal->id, 'estimation' => 20, 'epic_id' => $p2Epics[2]->id, 'sprint_id' => $p2Sprints[2]->id, 'status_index' => 3],
            ['name' => 'E-wallet Top-up Integration', 'content' => 'Integrasi top-up e-wallet: GoPay, OVO, Dana, ShopeePay. Implementasi direct debit dan QR code payment.', 'owner_id' => $users[7]->id, 'responsible_id' => $users[5]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityNormal->id, 'estimation' => 16, 'epic_id' => $p2Epics[2]->id, 'sprint_id' => $p2Sprints[2]->id, 'status_index' => 2],
            ['name' => 'Bug: OTP timeout terlalu cepat', 'content' => 'User melaporkan OTP expired terlalu cepat (30 detik). Perlu increase timeout ke 120 detik dan tambah resend OTP button dengan cooldown.', 'owner_id' => $users[0]->id, 'responsible_id' => $users[7]->id, 'type_id' => $typeBug->id, 'priority_id' => $priorityHigh->id, 'estimation' => 4, 'epic_id' => null, 'sprint_id' => null, 'status_index' => 1],
            ['name' => 'Investment Portfolio Dashboard', 'content' => 'Buat dashboard investasi: portfolio overview, gain/loss chart, asset allocation pie chart, dan market data real-time.', 'owner_id' => $users[1]->id, 'responsible_id' => $users[3]->id, 'type_id' => $typeEvolution->id, 'priority_id' => $priorityLow->id, 'estimation' => 32, 'epic_id' => $p2Epics[3]->id, 'sprint_id' => null, 'status_index' => 0],
            ['name' => 'Transaction History dengan Filter', 'content' => 'Implementasi transaction history dengan filter by date range, type, amount. Export ke PDF dan CSV. Pagination dengan infinite scroll.', 'owner_id' => $users[5]->id, 'responsible_id' => $users[0]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityNormal->id, 'estimation' => 12, 'epic_id' => $p2Epics[1]->id, 'sprint_id' => null, 'status_index' => 0],
        ];

        $allTickets = $allTickets->merge($this->insertTickets($projects[1], $p2Tickets, $p2Statuses));

        // ---- PROJECT 3: HR Management (8 tickets) ----
        $p3Statuses = $getStatuses($projects[2]);
        $p3Epics = $epics->where('project_id', $projects[2]->id)->values();

        $p3Tickets = [
            ['name' => 'Employee Registration Form', 'content' => 'Form registrasi karyawan baru: data pribadi, dokumen, emergency contact, bank account. Validasi NIK dan NPWP.', 'owner_id' => $users[2]->id, 'responsible_id' => $users[0]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 16, 'epic_id' => $p3Epics[0]->id, 'sprint_id' => null, 'status_index' => 2],
            ['name' => 'Organization Chart Generator', 'content' => 'Generate organization chart otomatis berdasarkan hierarki karyawan. Interactive chart dengan drill-down per department.', 'owner_id' => $users[0]->id, 'responsible_id' => $users[4]->id, 'type_id' => $typeEvolution->id, 'priority_id' => $priorityNormal->id, 'estimation' => 12, 'epic_id' => $p3Epics[0]->id, 'sprint_id' => null, 'status_index' => 1],
            ['name' => 'Payroll Calculation Engine', 'content' => 'Engine kalkulasi payroll: gaji pokok, tunjangan, potongan (BPJS, PPh21, pinjaman). Support komponen gaji custom per jabatan.', 'owner_id' => $users[2]->id, 'responsible_id' => $users[5]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 32, 'epic_id' => $p3Epics[1]->id, 'sprint_id' => null, 'status_index' => 1],
            ['name' => 'Slip Gaji PDF Generator', 'content' => 'Generate slip gaji PDF otomatis setiap bulan. Template customizable, batch processing, dan distribusi via email.', 'owner_id' => $users[4]->id, 'responsible_id' => $users[0]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityNormal->id, 'estimation' => 10, 'epic_id' => $p3Epics[1]->id, 'sprint_id' => null, 'status_index' => 0],
            ['name' => 'Leave Request & Approval Workflow', 'content' => 'Sistem pengajuan cuti: annual leave, sick leave, maternity leave. Multi-level approval workflow berdasarkan hierarki organisasi.', 'owner_id' => $users[2]->id, 'responsible_id' => $users[4]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 20, 'epic_id' => $p3Epics[2]->id, 'sprint_id' => null, 'status_index' => 1],
            ['name' => 'Leave Balance Dashboard', 'content' => 'Dashboard sisa cuti per karyawan: cuti tahunan, cuti sakit, cuti besar. Chart penggunaan cuti per bulan dan department summary.', 'owner_id' => $users[5]->id, 'responsible_id' => $users[2]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityNormal->id, 'estimation' => 8, 'epic_id' => $p3Epics[2]->id, 'sprint_id' => null, 'status_index' => 0],
            ['name' => 'Bug: PPh21 calculation error untuk TER', 'content' => 'Kalkulasi PPh21 dengan metode TER (Tarif Efektif Rata-rata) menghasilkan nilai yang salah untuk karyawan dengan PTKP K/3. Perlu review formula.', 'owner_id' => $users[0]->id, 'responsible_id' => $users[5]->id, 'type_id' => $typeBug->id, 'priority_id' => $priorityHigh->id, 'estimation' => 6, 'epic_id' => $p3Epics[1]->id, 'sprint_id' => null, 'status_index' => 1],
            ['name' => 'Attendance Integration via API', 'content' => 'Integrasi data kehadiran dari mesin fingerprint via REST API. Sync otomatis setiap jam, rekonsiliasi data, dan laporan keterlambatan.', 'owner_id' => $users[4]->id, 'responsible_id' => $users[0]->id, 'type_id' => $typeEvolution->id, 'priority_id' => $priorityLow->id, 'estimation' => 16, 'epic_id' => $p3Epics[0]->id, 'sprint_id' => null, 'status_index' => 0],
        ];

        $allTickets = $allTickets->merge($this->insertTickets($projects[2], $p3Tickets, $p3Statuses));

        // ---- PROJECT 4: LMS (10 tickets) ----
        $p4Statuses = $getStatuses($projects[3]);
        $p4Epics = $epics->where('project_id', $projects[3]->id)->values();
        $p4Sprints = $sprints->where('project_id', $projects[3]->id)->values();

        $p4Tickets = [
            ['name' => 'Course Creation Wizard', 'content' => 'Wizard multi-step untuk membuat course: basic info, curriculum builder, pricing, preview. Drag-and-drop untuk reorder modules.', 'owner_id' => $users[3]->id, 'responsible_id' => $users[6]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 24, 'epic_id' => $p4Epics[0]->id, 'sprint_id' => $p4Sprints[0]->id, 'status_index' => 2],
            ['name' => 'WYSIWYG Content Editor', 'content' => 'Rich text editor untuk course content: markdown, code highlighting, embedded video, formula math (LaTeX), dan image annotation.', 'owner_id' => $users[6]->id, 'responsible_id' => $users[7]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityNormal->id, 'estimation' => 16, 'epic_id' => $p4Epics[0]->id, 'sprint_id' => $p4Sprints[0]->id, 'status_index' => 2],
            ['name' => 'Video Upload & Transcoding', 'content' => 'Upload video dengan progress bar, background transcoding ke multiple resolution (360p, 720p, 1080p). Storage di S3 dengan CloudFront CDN.', 'owner_id' => $users[3]->id, 'responsible_id' => $users[1]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 28, 'epic_id' => $p4Epics[1]->id, 'sprint_id' => $p4Sprints[1]->id, 'status_index' => 2],
            ['name' => 'Adaptive Bitrate Streaming (HLS)', 'content' => 'Implementasi HLS streaming untuk adaptive video playback. Auto switch quality berdasarkan bandwidth. DRM protection untuk premium content.', 'owner_id' => $users[1]->id, 'responsible_id' => $users[6]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 20, 'epic_id' => $p4Epics[1]->id, 'sprint_id' => $p4Sprints[1]->id, 'status_index' => 1],
            ['name' => 'Quiz Engine dengan Multiple Question Types', 'content' => 'Engine quiz: multiple choice, true/false, fill-in-blank, essay, matching, ordering. Timer, auto-grading, dan detailed analytics.', 'owner_id' => $users[2]->id, 'responsible_id' => $users[7]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityNormal->id, 'estimation' => 24, 'epic_id' => $p4Epics[2]->id, 'sprint_id' => null, 'status_index' => 0],
            ['name' => 'Progress Tracking & Learning Path', 'content' => 'Track progress belajar per user: video watched percentage, quiz scores, module completion. Suggest next course berdasarkan learning path.', 'owner_id' => $users[7]->id, 'responsible_id' => $users[2]->id, 'type_id' => $typeEvolution->id, 'priority_id' => $priorityNormal->id, 'estimation' => 16, 'epic_id' => $p4Epics[2]->id, 'sprint_id' => null, 'status_index' => 0],
            ['name' => 'Certificate Generator', 'content' => 'Generate sertifikat PDF/image setelah menyelesaikan course. Template customizable, QR code verification, dan shareable link.', 'owner_id' => $users[3]->id, 'responsible_id' => $users[6]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityLow->id, 'estimation' => 12, 'epic_id' => $p4Epics[3]->id, 'sprint_id' => null, 'status_index' => 0],
            ['name' => 'Bug: Video buffering di koneksi lambat', 'content' => 'Video sering buffering di koneksi 3G. HLS manifest tidak optimal, segment duration terlalu panjang. Perlu optimasi encoding settings.', 'owner_id' => $users[6]->id, 'responsible_id' => $users[1]->id, 'type_id' => $typeBug->id, 'priority_id' => $priorityHigh->id, 'estimation' => 8, 'epic_id' => $p4Epics[1]->id, 'sprint_id' => null, 'status_index' => 0],
            ['name' => 'Discussion Forum per Course', 'content' => 'Forum diskusi per course: thread, reply, upvote/downvote, markdown support, notification. Moderasi oleh instructor.', 'owner_id' => $users[7]->id, 'responsible_id' => $users[3]->id, 'type_id' => $typeEvolution->id, 'priority_id' => $priorityLow->id, 'estimation' => 16, 'epic_id' => null, 'sprint_id' => null, 'status_index' => 0],
            ['name' => 'Instructor Analytics Dashboard', 'content' => 'Dashboard analytics untuk instructor: enrollment stats, completion rates, revenue, student feedback, quiz performance heatmap.', 'owner_id' => $users[3]->id, 'responsible_id' => $users[2]->id, 'type_id' => $typeEvolution->id, 'priority_id' => $priorityNormal->id, 'estimation' => 20, 'epic_id' => $p4Epics[3]->id, 'sprint_id' => null, 'status_index' => 0],
        ];

        $allTickets = $allTickets->merge($this->insertTickets($projects[3], $p4Tickets, $p4Statuses));

        // ---- PROJECT 5: IoT Dashboard (8 tickets) ----
        $p5Statuses = $getStatuses($projects[4]);
        $p5Epics = $epics->where('project_id', $projects[4]->id)->values();

        $p5Tickets = [
            ['name' => 'Device Registration API', 'content' => 'REST API untuk registrasi IoT device: device ID, type, firmware version, location. Token-based authentication per device.', 'owner_id' => $users[4]->id, 'responsible_id' => $users[0]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 16, 'epic_id' => $p5Epics[0]->id, 'sprint_id' => null, 'status_index' => 3],
            ['name' => 'MQTT Broker Setup', 'content' => 'Setup MQTT broker (Mosquitto) untuk komunikasi IoT device. Konfigurasi TLS, QoS levels, topic hierarchy, dan message persistence.', 'owner_id' => $users[4]->id, 'responsible_id' => $users[2]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 12, 'epic_id' => $p5Epics[1]->id, 'sprint_id' => null, 'status_index' => 3],
            ['name' => 'Time-series Data Ingestion Pipeline', 'content' => 'Data pipeline: MQTT → Kafka → InfluxDB. Handle high-throughput data ingestion (10K messages/sec), data validation, dan dead letter queue.', 'owner_id' => $users[2]->id, 'responsible_id' => $users[6]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 28, 'epic_id' => $p5Epics[1]->id, 'sprint_id' => null, 'status_index' => 3],
            ['name' => 'Real-time Dashboard dengan Grafana', 'content' => 'Setup Grafana dashboard untuk visualisasi data IoT: gauge, graph, heatmap, geo-map. Auto-refresh setiap 5 detik. Custom dark theme.', 'owner_id' => $users[6]->id, 'responsible_id' => $users[4]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityNormal->id, 'estimation' => 20, 'epic_id' => $p5Epics[2]->id, 'sprint_id' => null, 'status_index' => 3],
            ['name' => 'Custom Widget Builder', 'content' => 'Builder untuk custom widget dashboard: drag-and-drop, resize, data source selector, threshold coloring, dan widget templates.', 'owner_id' => $users[4]->id, 'responsible_id' => $users[2]->id, 'type_id' => $typeEvolution->id, 'priority_id' => $priorityNormal->id, 'estimation' => 24, 'epic_id' => $p5Epics[2]->id, 'sprint_id' => null, 'status_index' => 3],
            ['name' => 'Alert Rules Engine', 'content' => 'Engine untuk alert rules: threshold-based, anomaly detection, trend analysis. Notification via email, SMS, Slack, dan PagerDuty.', 'owner_id' => $users[0]->id, 'responsible_id' => $users[6]->id, 'type_id' => $typeTask->id, 'priority_id' => $priorityHigh->id, 'estimation' => 20, 'epic_id' => $p5Epics[3]->id, 'sprint_id' => null, 'status_index' => 3],
            ['name' => 'OTA Firmware Update System', 'content' => 'Sistem OTA update firmware untuk IoT devices: versioning, staged rollout, rollback mechanism, dan update progress monitoring.', 'owner_id' => $users[6]->id, 'responsible_id' => $users[0]->id, 'type_id' => $typeEvolution->id, 'priority_id' => $priorityNormal->id, 'estimation' => 24, 'epic_id' => $p5Epics[0]->id, 'sprint_id' => null, 'status_index' => 3],
            ['name' => 'Bug: Data loss saat network reconnect', 'content' => 'Device kehilangan data saat network disconnect dan reconnect. MQTT QoS 1 tidak cukup, perlu implementasi local buffer di device dan QoS 2 untuk critical data.', 'owner_id' => $users[2]->id, 'responsible_id' => $users[4]->id, 'type_id' => $typeBug->id, 'priority_id' => $priorityHigh->id, 'estimation' => 10, 'epic_id' => $p5Epics[1]->id, 'sprint_id' => null, 'status_index' => 3],
        ];

        $allTickets = $allTickets->merge($this->insertTickets($projects[4], $p5Tickets, $p5Statuses));

        return $allTickets;
    }

    /**
     * Insert tickets using DB::table to avoid boot events (notifications, auto-code generation)
     */
    private function insertTickets($project, $ticketsData, $statuses)
    {
        $tickets = collect();
        $existingCount = Ticket::where('project_id', $project->id)->count();

        foreach ($ticketsData as $index => $data) {
            $order = $existingCount + $index;
            $code = $project->ticket_prefix . '-' . ($existingCount + $index + 1);
            $statusIndex = $data['status_index'];
            unset($data['status_index']);

            $statusId = $statuses->values()[$statusIndex]->id ?? $statuses->first()->id;

            $id = DB::table('tickets')->insertGetId([
                'name' => $data['name'],
                'content' => $data['content'],
                'owner_id' => $data['owner_id'],
                'responsible_id' => $data['responsible_id'],
                'status_id' => $statusId,
                'project_id' => $project->id,
                'code' => $code,
                'order' => $order,
                'type_id' => $data['type_id'],
                'priority_id' => $data['priority_id'],
                'estimation' => $data['estimation'],
                'epic_id' => $data['epic_id'],
                'sprint_id' => $data['sprint_id'],
                'created_at' => now()->subDays(rand(5, 60)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);

            $tickets->push(Ticket::find($id));
        }

        return $tickets;
    }

    // ================================================================
    // TICKET COMMENTS
    // ================================================================
    private function createTicketComments($tickets, $users)
    {
        $commentTemplates = [
            'Saya sudah mulai mengerjakan ini. Akan update progress di sini.',
            'Bisa tolong review PR-nya? Sudah saya push ke branch feature.',
            'Saya menemukan issue terkait. Mungkin perlu diskusi lebih lanjut.',
            'Sudah tested di staging, berjalan sesuai ekspektasi. ✅',
            'Ada perubahan requirement dari stakeholder. Mohon cek note di confluence.',
            'Unit test sudah ditambahkan, coverage 87%. Siap untuk code review.',
            'Saya perlu bantuan untuk bagian integrasi API. Ada yang bisa pair programming?',
            'Sudah refactor code sesuai feedback. Mohon review ulang. 🔄',
            'Deployment ke staging berhasil. QA team bisa mulai testing.',
            'Menemukan edge case yang belum ter-handle. Akan fix di commit berikutnya.',
            'Performance test menunjukkan response time < 200ms. Sesuai target. 🚀',
            'Dokumentasi API sudah diupdate di Swagger. Silakan cek.',
            'Database migration sudah dibuat. Perlu dijalankan sebelum deploy.',
            'Blocker: menunggu API key dari vendor pihak ketiga.',
            'Sudah resolve conflict dengan branch develop. Ready to merge.',
            'Estimasi perlu ditambah karena scope berubah. Request 8 jam tambahan.',
            'Screenshot hasil testing terlampir. Semua scenario pass.',
            'Ini terkait dengan ticket sebelumnya. Perlu coordinate agar tidak duplicate.',
            'Bug sudah di-reproduce secara konsisten. Root cause ditemukan di layer service.',
            'Rollback plan sudah disiapkan kalau ada issue saat deployment.',
        ];

        foreach ($tickets as $ticket) {
            $numComments = rand(1, 4);
            $selectedComments = collect($commentTemplates)->random($numComments);

            foreach ($selectedComments as $i => $comment) {
                $commenter = $users[array_rand($users)];
                DB::table('ticket_comments')->insert([
                    'user_id' => $commenter->id,
                    'ticket_id' => $ticket->id,
                    'content' => $comment,
                    'created_at' => $ticket->created_at->addDays($i + 1)->addHours(rand(8, 17)),
                    'updated_at' => $ticket->created_at->addDays($i + 1)->addHours(rand(8, 17)),
                ]);
            }
        }
    }

    // ================================================================
    // TICKET HOURS
    // ================================================================
    private function createTicketHours($tickets, $users, $activities)
    {
        foreach ($tickets as $ticket) {
            if (!$ticket->responsible_id) continue;

            $numLogs = rand(1, 4);
            for ($i = 0; $i < $numLogs; $i++) {
                $logUser = ($i === 0) ? $ticket->responsible_id : $users[array_rand($users)]->id;
                $value = round(rand(5, 40) / 10, 1); // 0.5 to 4.0 hours
                $activity = $activities->random();

                $logComments = [
                    'Implementasi fitur dasar',
                    'Code review dan refactoring',
                    'Debugging dan fix issues',
                    'Writing unit tests',
                    'Setup dan konfigurasi',
                    'Research dan prototyping',
                    'Meeting dan diskusi teknis',
                    'Documentation update',
                    'Performance optimization',
                    'Integration testing',
                ];

                DB::table('ticket_hours')->insert([
                    'user_id' => $logUser,
                    'ticket_id' => $ticket->id,
                    'value' => $value,
                    'comment' => $logComments[array_rand($logComments)],
                    'activity_id' => $activity->id,
                    'created_at' => $ticket->created_at->addDays(rand(1, 10))->addHours(rand(9, 17)),
                    'updated_at' => $ticket->created_at->addDays(rand(1, 10))->addHours(rand(9, 17)),
                ]);
            }
        }
    }

    // ================================================================
    // TICKET ACTIVITIES (STATUS CHANGES)
    // ================================================================
    private function createTicketActivities($tickets, $users, $ticketStatuses)
    {
        foreach ($tickets as $ticket) {
            $projectStatuses = TicketStatus::where('project_id', $ticket->project_id)->get();
            if ($projectStatuses->isEmpty()) {
                $projectStatuses = $ticketStatuses;
            }

            $currentStatusOrder = $projectStatuses->search(function ($s) use ($ticket) {
                return $s->id === $ticket->status_id;
            });

            if ($currentStatusOrder === false || $currentStatusOrder === 0) continue;

            // Create activity trail from first status to current
            $statusTrail = $projectStatuses->take($currentStatusOrder + 1);
            $activityUser = $ticket->responsible_id ?? $ticket->owner_id;

            for ($i = 0; $i < count($statusTrail) - 1; $i++) {
                DB::table('ticket_activities')->insert([
                    'ticket_id' => $ticket->id,
                    'old_status_id' => $statusTrail[$i]->id,
                    'new_status_id' => $statusTrail[$i + 1]->id,
                    'user_id' => $activityUser,
                    'created_at' => $ticket->created_at->addDays($i + 1)->addHours(rand(9, 17)),
                    'updated_at' => $ticket->created_at->addDays($i + 1)->addHours(rand(9, 17)),
                ]);
            }
        }
    }

    // ================================================================
    // TICKET RELATIONS
    // ================================================================
    private function createTicketRelations($tickets)
    {
        $types = ['related_to', 'blocked_by', 'duplicate_of'];

        // Group tickets by project
        $ticketsByProject = $tickets->groupBy('project_id');

        foreach ($ticketsByProject as $projectId => $projectTickets) {
            if ($projectTickets->count() < 3) continue;

            $numRelations = min(3, intdiv($projectTickets->count(), 2));

            for ($i = 0; $i < $numRelations; $i++) {
                $ticket = $projectTickets[$i];
                $relatedIndex = $projectTickets->count() - 1 - $i;
                $relatedTicket = $projectTickets[$relatedIndex];

                if ($ticket->id === $relatedTicket->id) continue;

                // Avoid duplicate relations
                $exists = DB::table('ticket_relations')
                    ->where('ticket_id', $ticket->id)
                    ->where('relation_id', $relatedTicket->id)
                    ->exists();

                if (!$exists) {
                    DB::table('ticket_relations')->insert([
                        'ticket_id' => $ticket->id,
                        'relation_id' => $relatedTicket->id,
                        'type' => $types[$i % count($types)],
                        'sort' => $i + 1,
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => now()->subDays(rand(0, 5)),
                    ]);
                }
            }
        }
    }

    // ================================================================
    // TICKET SUBSCRIBERS
    // ================================================================
    private function createTicketSubscribers($tickets, $users)
    {
        foreach ($tickets as $ticket) {
            // Subscribe 1-3 random users to each ticket
            $numSubscribers = rand(1, 3);
            $subscriberUsers = collect($users)->random($numSubscribers);

            foreach ($subscriberUsers as $user) {
                DB::table('ticket_subscribers')->insertOrIgnore([
                    'user_id' => $user->id,
                    'ticket_id' => $ticket->id,
                    'created_at' => $ticket->created_at->addHours(rand(1, 24)),
                    'updated_at' => $ticket->created_at->addHours(rand(1, 24)),
                ]);
            }
        }
    }
}
