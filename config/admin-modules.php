<?php

return [

    'actions' => [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
    ],

    'groups' => [
        '' => ['dashboard'],
        'Catalog' => ['categories', 'instructors', 'courses', 'bundles', 'live_sessions'],
        'Learners' => ['users', 'roles', 'imports', 'course_progress'],
        'Assessments' => ['quizzes', 'quiz_results'],
        'Analytics' => ['reports'],
        'Site' => ['settings', 'partners'],
    ],

    'modules' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'actions' => ['view'],
            'nav' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'admin.dashboard',
                    'icon' => 'dashboard',
                    'active' => 'admin.dashboard',
                ],
            ],
        ],
        'categories' => [
            'label' => 'Categories',
            'icon' => 'folder',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'nav' => [
                [
                    'label' => 'Categories',
                    'route' => 'admin.categories.index',
                    'icon' => 'folder',
                    'active' => 'admin.categories.*',
                ],
            ],
        ],
        'instructors' => [
            'label' => 'Instructors',
            'icon' => 'instructor',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'nav' => [
                [
                    'label' => 'Instructors',
                    'route' => 'admin.instructors.index',
                    'icon' => 'instructor',
                    'active' => 'admin.instructors.*',
                ],
            ],
        ],
        'courses' => [
            'label' => 'Courses',
            'icon' => 'book',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'nav' => [
                [
                    'label' => 'Courses',
                    'route' => 'admin.courses.index',
                    'icon' => 'book',
                    'active' => ['admin.courses.*', 'admin.chapters.*', 'admin.lessons.*'],
                ],
            ],
        ],
        'bundles' => [
            'label' => 'Bundles',
            'icon' => 'bundle',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'nav' => [
                [
                    'label' => 'Bundles',
                    'route' => 'admin.bundles.index',
                    'icon' => 'bundle',
                    'active' => 'admin.bundles.*',
                ],
            ],
        ],
        'live_sessions' => [
            'label' => 'Live Sessions',
            'icon' => 'live',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'nav' => [
                [
                    'label' => 'Live sessions',
                    'route' => 'admin.live-sessions.index',
                    'icon' => 'live',
                    'active' => 'admin.live-sessions.*',
                ],
            ],
        ],
        'users' => [
            'label' => 'Users',
            'icon' => 'users',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'nav' => [
                [
                    'label' => 'Users',
                    'route' => 'admin.users.index',
                    'icon' => 'users',
                    'active' => 'admin.users.*',
                ],
            ],
        ],
        'roles' => [
            'label' => 'Roles & Permissions',
            'icon' => 'shield',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'nav' => [
                [
                    'label' => 'Roles & permissions',
                    'route' => 'admin.roles.index',
                    'icon' => 'shield',
                    'active' => 'admin.roles.*',
                ],
            ],
        ],
        'imports' => [
            'label' => 'Import Students',
            'icon' => 'import',
            'actions' => ['view', 'create'],
            'nav' => [
                [
                    'label' => 'Import students',
                    'route' => 'admin.imports.students.create',
                    'icon' => 'import',
                    'active' => 'admin.imports.students.*',
                ],
            ],
        ],
        'course_progress' => [
            'label' => 'Student Progress',
            'icon' => 'pulse',
            'actions' => ['view'],
            'nav' => [
                [
                    'label' => 'Student progress',
                    'route' => 'admin.course-progress.index',
                    'icon' => 'pulse',
                    'active' => 'admin.course-progress.*',
                ],
            ],
        ],
        'quizzes' => [
            'label' => 'Quizzes',
            'icon' => 'quiz',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'nav' => [
                [
                    'label' => 'Quizzes',
                    'route' => 'admin.quizzes.index',
                    'icon' => 'quiz',
                    'active' => ['admin.quizzes.*', 'admin.questions.*'],
                ],
            ],
        ],
        'quiz_results' => [
            'label' => 'Quiz Results',
            'icon' => 'results',
            'actions' => ['view'],
            'nav' => [
                [
                    'label' => 'Quiz results',
                    'route' => 'admin.quiz-results.index',
                    'icon' => 'results',
                    'active' => 'admin.quiz-results.*',
                ],
            ],
        ],
        'reports' => [
            'label' => 'Reports',
            'icon' => 'chart',
            'actions' => ['view'],
            'nav' => [
                [
                    'label' => 'Reports & Analytics',
                    'route' => 'admin.reports.index',
                    'icon' => 'chart',
                    'active' => 'admin.reports.*',
                ],
            ],
        ],
        'settings' => [
            'label' => 'Settings',
            'icon' => 'settings',
            'actions' => ['view', 'edit', 'delete'],
            'nav' => [
                [
                    'label' => 'Settings',
                    'route' => 'admin.settings.edit',
                    'icon' => 'settings',
                    'active' => 'admin.settings.*',
                ],
                [
                    'label' => 'Contact messages',
                    'route' => 'admin.contact-messages.index',
                    'icon' => 'mail',
                    'active' => 'admin.contact-messages.*',
                ],
            ],
        ],
        'partners' => [
            'label' => 'Partner Logos',
            'icon' => 'image',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'nav' => [
                [
                    'label' => 'Partner logos',
                    'route' => 'admin.partners.index',
                    'icon' => 'image',
                    'active' => 'admin.partners.*',
                ],
            ],
        ],
    ],

    'route_modules' => [
        'admin.dashboard' => 'dashboard',
        'admin.categories' => 'categories',
        'admin.instructors' => 'instructors',
        'admin.courses.chapters' => 'courses',
        'admin.courses' => 'courses',
        'admin.chapters.lessons' => 'courses',
        'admin.chapters' => 'courses',
        'admin.lessons' => 'courses',
        'admin.bundles' => 'bundles',
        'admin.live-sessions' => 'live_sessions',
        'admin.users' => 'users',
        'admin.roles' => 'roles',
        'admin.imports' => 'imports',
        'admin.course-progress' => 'course_progress',
        'admin.quizzes.questions' => 'quizzes',
        'admin.quizzes' => 'quizzes',
        'admin.questions' => 'quizzes',
        'admin.quiz-results' => 'quiz_results',
        'admin.reports' => 'reports',
        'admin.settings' => 'settings',
        'admin.partners' => 'partners',
        'admin.contact-messages' => 'settings',
    ],

    'route_actions' => [
        'admin.imports.students.create' => 'view',
        'admin.imports.students.preview' => 'create',
        'admin.imports.students.store' => 'create',
        'admin.settings.edit' => 'view',
        'admin.settings.update' => 'edit',
        'admin.courses.enrolled-users' => 'view',
        'admin.users.status' => 'edit',
        'admin.contact-messages.destroy-all' => 'delete',
    ],

];
