<?php

namespace App\Config;

// Base paths
define('BASE_URL', 'http://localhost'); // Absolute base URL for local development
define('BASE_PATH', '/studyaid/');
define('APP_URL', BASE_URL . BASE_PATH); // Convenience absolute URL prefix
define('APP_PATH', 'app/views/');
define('VIEWS_PATH', 'app/views/');
define('LEARNING_VIEWS_PATH', VIEWS_PATH . 'learningView/');
define('AUTH_VIEWS_PATH', VIEWS_PATH . 'authView/');

// Public assets
define('PUBLIC_PATH', BASE_PATH . 'public/');
define('CSS_PATH', PUBLIC_PATH . 'css/');
define('JS_PATH', PUBLIC_PATH . 'js/');
define('ASSET_PATH', PUBLIC_PATH . 'asset/');

// Images
define('IMG_VISIBLE', ASSET_PATH . 'visible.png');
define('IMG_INVISIBLE', ASSET_PATH . 'invisible.png');
define('IMG_LOGO', ASSET_PATH . 'logo.png');
define('IMG_SETTING', ASSET_PATH . 'setting.png');
define('IMG_CARET_DOWN', ASSET_PATH . 'down-chevron.png');

// ============================================================================
// AUTH ROUTES
// ============================================================================
define('HOME', BASE_PATH . 'auth/home');
define('LOGIN', BASE_PATH . 'auth/login');
define('REGISTER', BASE_PATH . 'auth/register');
define('LOGOUT', BASE_PATH . 'auth/logout');

//Email
define('EMAIL_FORGOT_PASSWORD', BASE_PATH . 'auth/forgotPassword');
// ============================================================================
// USER ROUTES
// ============================================================================
define('DASHBOARD', BASE_PATH . 'user/dashboard');
define('PROFILE', BASE_PATH . 'user/profile');
define('UPDATE_PROFILE', BASE_PATH . 'user/updateProfile');

// ============================================================================
// LEARNING MATERIAL ROUTES
// ============================================================================
define('NEW_DOCUMENT', BASE_PATH . 'lm/newDocument');
define('UPLOAD_DOCUMENT', BASE_PATH . 'lm/uploadDocument');
define('NEW_FOLDER', BASE_PATH . 'lm/newFolder');
define('CREATE_FOLDER', BASE_PATH . 'lm/createFolder');
define('DISPLAY_LEARNING_MATERIALS', BASE_PATH . 'lm/displayLearningMaterials');
define('DISPLAY_DOCUMENT', BASE_PATH . 'lm/displayDocument');
define('DELETE_DOCUMENT', BASE_PATH . 'lm/deleteDocument');
define('DELETE_FOLDER', BASE_PATH . 'lm/deleteFolder');

// Summary routes
define('SUMMARY', BASE_PATH . 'lm/summary');
define('CREATE_SUMMARY', BASE_PATH . 'lm/createSummary');
define('GENERATE_SUMMARY', BASE_PATH . 'lm/generateSummary');
define('DELETE_SUMMARY', BASE_PATH . 'lm/deleteSummary');
define('SAVE_SUMMARY_AS_FILE', BASE_PATH . 'lm/saveSummaryAsFile');
define('EXPORT_SUMMARY_PDF', BASE_PATH . 'lm/exportSummaryAsPdf');
define('EXPORT_SUMMARY_DOCX', BASE_PATH . 'lm/exportSummaryAsDocx');
define('EXPORT_SUMMARY_TXT', BASE_PATH . 'lm/exportSummaryAsTxt');

// Note routes
define('NOTE', BASE_PATH . 'lm/note');
define('GENERATE_NOTES', BASE_PATH . 'lm/generateNotes');
define('SAVE_NOTE', BASE_PATH . 'lm/saveNote');
define('DELETE_NOTE', BASE_PATH . 'lm/deleteNote');
define('SAVE_NOTE_AS_FILE', BASE_PATH . 'lm/saveNoteAsFile');
define('EXPORT_NOTE_PDF', BASE_PATH . 'lm/exportNoteAsPdf');
define('EXPORT_NOTE_DOCX', BASE_PATH . 'lm/exportNoteAsDocx');
define('EXPORT_NOTE_TXT', BASE_PATH . 'lm/exportNoteAsTxt');

// Mindmap routes
define('MINDMAP', BASE_PATH . 'lm/mindmap');
define('GENERATE_MINDMAP', BASE_PATH . 'lm/generateMindmap');
define('VIEW_MINDMAP_ROUTE', BASE_PATH . 'lm/viewMindmap');
define('DELETE_MINDMAP', BASE_PATH . 'lm/deleteMindmap');

// Chatbot, Quiz, Flashcard routes
define('CHATBOT', BASE_PATH . 'lm/chatbot');
define('QUIZ', BASE_PATH . 'lm/quiz');
define('VIEW_QUIZ_ROUTE', BASE_PATH . 'lm/viewQuiz');
define('SAVE_SCORE', BASE_PATH . 'lm/saveScore');
define('FLASHCARD', BASE_PATH . 'lm/flashcard');
define('GENERATE_QUIZ', BASE_PATH . 'lm/generateQuiz');
define('SUBMIT_QUIZ', BASE_PATH . 'lm/submitQuiz');
define('GENERATE_FLASHCARDS', BASE_PATH . 'lm/generateFlashcards');
define('SEND_CHAT_MESSAGE', BASE_PATH . 'lm/sendQuestionChat');

// JSON API routes
define('RENAME_FOLDER', BASE_PATH . 'lm/renameFolder');
define('RENAME_FILE', BASE_PATH . 'lm/renameFile');
define('MOVE_FILE', BASE_PATH . 'lm/moveFile');
define('MOVE_FOLDER', BASE_PATH . 'lm/moveFolder');

// ============================================================================
// VIEW FILE PATHS (for require_once/include)
// ============================================================================
define('VIEW_SIDEBAR', VIEWS_PATH . 'sidebar.php');
define('VIEW_DASHBOARD', VIEWS_PATH . 'dashboardView.php');
define('VIEW_PROFILE', VIEWS_PATH . 'profileView.php');

// Auth views
define('VIEW_LOGIN', AUTH_VIEWS_PATH . 'login.php');
define('VIEW_REGISTER', AUTH_VIEWS_PATH . 'register.php');
define('VIEW_HOME', AUTH_VIEWS_PATH . 'home.php');

// Learning views
define('VIEW_NEW_DOCUMENT', LEARNING_VIEWS_PATH . 'newDocument.php');
define('VIEW_NEW_FOLDER', LEARNING_VIEWS_PATH . 'newFolder.php');
define('VIEW_ALL_DOCUMENT', LEARNING_VIEWS_PATH . 'allDocument.php');
define('VIEW_DISPLAY_DOCUMENT', LEARNING_VIEWS_PATH . 'displayDocument.php');
define('VIEW_SUMMARY', LEARNING_VIEWS_PATH . 'summary.php');
define('VIEW_NOTE', LEARNING_VIEWS_PATH . 'note.php');
define('VIEW_MINDMAP', LEARNING_VIEWS_PATH . 'mindmap.php');
define('VIEW_CREATE_SUMMARY', LEARNING_VIEWS_PATH . 'createSummary.php');
define('VIEW_CHATBOT', LEARNING_VIEWS_PATH . 'chatbot.php');
define('VIEW_QUIZ', LEARNING_VIEWS_PATH . 'quiz.php');
define('VIEW_FLASHCARD', LEARNING_VIEWS_PATH . 'flashcard.php');
define('VIEW_NAVBAR', LEARNING_VIEWS_PATH . 'navbar.php');
define('VIEW_MULTI_DOCUMENT', LEARNING_VIEWS_PATH . 'multidoc.php');
// ============================================================================
// URL PATTERNS FOR isActive() FUNCTION
// ============================================================================
define('URL_DASHBOARD', 'index.php?url=user/dashboard');
define('URL_NEW_DOCUMENT', 'index.php?url=lm/newDocument');
define('URL_NEW_FOLDER', 'index.php?url=lm/newFolder');
define('URL_DISPLAY_LEARNING_MATERIALS', 'index.php?url=lm/displayLearningMaterials');
define('URL_CREATE_SUMMARY', 'index.php?url=lm/createSummary');
define('URL_SUMMARY', 'index.php?url=lm/summary');
define('URL_NOTE', 'index.php?url=lm/note');
define('URL_MINDMAP', 'index.php?url=lm/mindmap');
define('URL_CHATBOT', 'index.php?url=lm/chatbot');
define('URL_FLASHCARD', 'index.php?url=lm/flashcard');
define('URL_QUIZ', 'index.php?url=lm/quiz');