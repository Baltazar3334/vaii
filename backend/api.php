<?php

// Nastavenie CORS hlavičiek pre komunikáciu s frontendom
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Konfigurácia session a spustenie relácie
ini_set('session.cookie_samesite', 'Lax');
session_start();

// Spracovanie predbežnej požiadavky OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Import potrebných tried pre databázu a kontroléry
require_once 'db.php';
require_once 'QuizController.php';
require_once 'AuthController.php';

// Inicializácia inštancií tried
$db = Database::getInstance();
$quizCtrl = new QuizController($db);
$authCtrl = new AuthController($db);

// Získanie akcie z URL parametra a dát z tela požiadavky
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$data = json_decode(file_get_contents("php://input"), true);

// Zoznam akcií, ktoré vyžadujú prihláseného používateľa
$protected_actions = ['save_quiz', 'delete_quiz', 'reset_account'];

// Kontrola autorizácie pre chránené akcie
if (in_array($action, $protected_actions) && !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Smerovanie požiadaviek na základe parametra action
switch ($action) {
    case 'login':
        echo json_encode($authCtrl->login($data['email'], $data['password']));
        break;
    case 'register':
        echo json_encode($authCtrl->register($data['username'], $data['email'], $data['password']));
        break;
    case 'get_all_quizzes':
        echo json_encode($quizCtrl->getAllPublic());
        break;
    case 'get_user_quizzes':
        echo json_encode($quizCtrl->getUserQuizzes($_GET['user_id'] ?? 0));
        break;
    case 'increment_plays':
        echo json_encode($quizCtrl->incrementPlays($_GET['id'] ?? 0));
        break;
    case 'get_leaderboard':
        echo json_encode($quizCtrl->getLeaderboard());
        break;
    case 'get_quiz_details':
        echo json_encode($quizCtrl->getDetails($_GET['id'] ?? 0));
        break;
    case 'save_quiz':
        echo json_encode($quizCtrl->saveQuiz($data, $data['quiz_id'] ?? null));
        break;
    case 'delete_quiz':
        echo json_encode($quizCtrl->delete($data['quiz_id'], $data['user_id']));
        break;
    case 'reset_account':
        echo json_encode($quizCtrl->resetAccount($data['user_id']));
        break;
    case 'update_avatar':
        echo json_encode($authCtrl->updateAvatar($data['user_id'], $data['avatar_url']));
        break;
    case 'update_username':
        echo json_encode($authCtrl->updateUsername($data['user_id'], $data['new_username']));
        break;
    case 'update_password':
        echo json_encode($authCtrl->updatePassword($data['user_id'], $data['new_password']));
        break;
    case 'check_auth':
        // Overenie aktuálneho stavu prihlásenia zo session
        echo json_encode(['logged_in' => isset($_SESSION['user_id']), 'user' => $_SESSION['user_id'] ? ['id' => $_SESSION['user_id'], 'username' => $_SESSION['username']] : null]);
        break;
    default:
        // Odpoveď v prípade neznámej akcie
        echo json_encode(['success' => false, 'message' => 'Action not found']);
}