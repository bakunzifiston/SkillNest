<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Serve course images and lesson uploads from storage (works without storage:link)
Route::get('course-image/{path}', [\App\Http\Controllers\StorageController::class, 'courseImage'])->where('path', '.*')->name('course.image');
Route::get('lesson-file/{path}', [\App\Http\Controllers\StorageController::class, 'lessonFile'])->where('path', '.*')->name('lesson.file');

// Public site
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/courses', [PageController::class, 'courses'])->name('courses.index');
Route::get('/courses/{course}', [\App\Http\Controllers\CourseController::class, 'show'])->name('courses.show');
Route::post('/courses/{course}/enroll', [\App\Http\Controllers\CourseController::class, 'enroll'])->name('courses.enroll');
Route::get('/bundles', [\App\Http\Controllers\BundleController::class, 'index'])->name('bundles.index');
Route::get('/bundles/{bundle}', [\App\Http\Controllers\BundleController::class, 'show'])->name('bundles.show');
Route::post('/bundles/{bundle}/enroll', [\App\Http\Controllers\BundleController::class, 'enroll'])->name('bundles.enroll');
Route::get('/my-bundles', [\App\Http\Controllers\BundleController::class, 'myBundles'])->name('bundles.my-bundles')->middleware('auth');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'contactSubmit'])->name('contact.submit');

// Learner: my courses, lessons, quizzes (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/my-courses', [\App\Http\Controllers\CourseController::class, 'myCourses'])->name('courses.my-courses');
    Route::get('/courses/{course}/lessons/{lesson}', [\App\Http\Controllers\CourseController::class, 'showLesson'])->name('courses.lessons.show');
    Route::post('/lessons/{lesson}/complete', [\App\Http\Controllers\CourseController::class, 'completeLesson'])->name('lessons.complete');
    Route::get('/courses/{course}/quizzes/{quiz}', [\App\Http\Controllers\QuizController::class, 'show'])->name('courses.quizzes.show');
    Route::post('/courses/{course}/quizzes/{quiz}/start', [\App\Http\Controllers\QuizController::class, 'start'])->name('courses.quizzes.start');
    Route::get('/quizzes/attempts/{quizAttempt}', [\App\Http\Controllers\QuizController::class, 'take'])->name('quizzes.take');
    Route::post('/quizzes/attempts/{quizAttempt}/submit', [\App\Http\Controllers\QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('/quizzes/attempts/{quizAttempt}/result', [\App\Http\Controllers\QuizController::class, 'result'])->name('quizzes.result');
});

// Learner dashboard
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->canAccessAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    $courseEnrollments = $user->enrollments()
        ->with(['course.category', 'course.chapters.lessons'])
        ->latest()
        ->take(6)
        ->get();

    $bundleEnrollments = $user->bundleEnrollments()
        ->with('bundle')
        ->latest('enrolled_at')
        ->take(5)
        ->get();

    return view('dashboard', compact('courseEnrollments', 'bundleEnrollments'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Super Admin panel
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
    Route::get('/dashboard', \App\Http\Controllers\Admin\DashboardController::class)->name('dashboard');
    Route::post('contact-messages/destroy-all', [\App\Http\Controllers\Admin\ContactMessageController::class, 'destroyAll'])->name('contact-messages.destroy-all');
    Route::resource('contact-messages', \App\Http\Controllers\Admin\ContactMessageController::class)->only(['index', 'show', 'destroy']);
    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::patch('users/{user}/status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.status');
    Route::delete('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->except(['show']);
    Route::get('imports/students', [\App\Http\Controllers\Admin\StudentImportController::class, 'create'])->name('imports.students.create');
    Route::post('imports/students/preview', [\App\Http\Controllers\Admin\StudentImportController::class, 'preview'])->name('imports.students.preview');
    Route::post('imports/students', [\App\Http\Controllers\Admin\StudentImportController::class, 'store'])->name('imports.students.store');
    Route::get('course-progress', [\App\Http\Controllers\Admin\CourseProgressController::class, 'index'])->name('course-progress.index');
    Route::get('course-progress/{course}', [\App\Http\Controllers\Admin\CourseProgressController::class, 'show'])->name('course-progress.show');
    Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::resource('partners', \App\Http\Controllers\Admin\PartnerController::class)->except(['show']);
    Route::resource('bundles', \App\Http\Controllers\Admin\BundleController::class)->except(['show']);
    Route::post('bundles/{bundle}/courses', [\App\Http\Controllers\Admin\BundleController::class, 'addCourse'])->name('bundles.courses.add');
    Route::delete('bundles/{bundle}/courses/{course}', [\App\Http\Controllers\Admin\BundleController::class, 'removeCourse'])->name('bundles.courses.remove');
    Route::put('bundles/{bundle}/courses-order', [\App\Http\Controllers\Admin\BundleController::class, 'updateCourseOrder'])->name('bundles.courses.order');
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->except(['show']);
    Route::resource('instructors', \App\Http\Controllers\Admin\InstructorController::class)->except(['show']);
    Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class)->except(['show']);
    Route::patch('courses/{course}/status', [\App\Http\Controllers\Admin\CourseController::class, 'updateStatus'])->name('courses.status');
    Route::get('reports', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('reports.index');
    Route::get('quiz-results', [\App\Http\Controllers\Admin\QuizResultsController::class, 'index'])->name('quiz-results.index');
    Route::get('quiz-results/export', [\App\Http\Controllers\Admin\QuizResultsController::class, 'export'])->name('quiz-results.export');
    Route::get('quiz-results/{quizAttempt}', [\App\Http\Controllers\Admin\QuizResultsController::class, 'show'])->name('quiz-results.show');
    Route::get('courses/{id}/enrolled-users', [\App\Http\Controllers\Admin\EnrolledUsersController::class, '__invoke'])->name('courses.enrolled-users');
    Route::resource('live-sessions', \App\Http\Controllers\Admin\LiveSessionController::class);
    Route::resource('quizzes', \App\Http\Controllers\Admin\QuizController::class)->except(['show']);
    Route::get('quizzes/{quiz}/questions', [\App\Http\Controllers\Admin\QuestionController::class, 'index'])->name('quizzes.questions.index');
    Route::get('quizzes/{quiz}/questions/create', [\App\Http\Controllers\Admin\QuestionController::class, 'create'])->name('quizzes.questions.create');
    Route::post('quizzes/{quiz}/questions', [\App\Http\Controllers\Admin\QuestionController::class, 'store'])->name('quizzes.questions.store');
    Route::get('questions/{question}/edit', [\App\Http\Controllers\Admin\QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('questions/{question}', [\App\Http\Controllers\Admin\QuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{question}', [\App\Http\Controllers\Admin\QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::get('courses/{course}/chapters', [\App\Http\Controllers\Admin\ChapterController::class, 'index'])->name('courses.chapters.index');
    Route::get('courses/{course}/chapters/create', [\App\Http\Controllers\Admin\ChapterController::class, 'create'])->name('courses.chapters.create');
    Route::post('courses/{course}/chapters', [\App\Http\Controllers\Admin\ChapterController::class, 'store'])->name('courses.chapters.store');
    Route::get('chapters/{chapter}/edit', [\App\Http\Controllers\Admin\ChapterController::class, 'edit'])->name('chapters.edit');
    Route::put('chapters/{chapter}', [\App\Http\Controllers\Admin\ChapterController::class, 'update'])->name('chapters.update');
    Route::delete('chapters/{chapter}', [\App\Http\Controllers\Admin\ChapterController::class, 'destroy'])->name('chapters.destroy');
    Route::get('chapters/{chapter}/lessons', [\App\Http\Controllers\Admin\LessonController::class, 'index'])->name('chapters.lessons.index');
    Route::get('chapters/{chapter}/lessons/create', [\App\Http\Controllers\Admin\LessonController::class, 'create'])->name('chapters.lessons.create');
    Route::post('chapters/{chapter}/lessons', [\App\Http\Controllers\Admin\LessonController::class, 'store'])->name('chapters.lessons.store');
    Route::get('lessons/{lesson}/edit', [\App\Http\Controllers\Admin\LessonController::class, 'edit'])->name('lessons.edit');
    Route::put('lessons/{lesson}', [\App\Http\Controllers\Admin\LessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{lesson}', [\App\Http\Controllers\Admin\LessonController::class, 'destroy'])->name('lessons.destroy');
});

require __DIR__.'/auth.php';
