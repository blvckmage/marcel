<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

// Загрузка пользователей
$users_file = __DIR__ . '/forum_users.json';
$users = [];
if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true) ?: [];
}

$user = $users[$_SESSION['user_id']] ?? null;
if (!$user || !isset($user['role']) || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Недостаточно прав']);
    exit;
}

// Проверка на бан
if (isset($user['banned']) && $user['banned']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Аккаунт заблокирован']);
    exit;
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit;
}

// Получение данных
$topic_id = $_POST['topic_id'] ?? '';
$post_index = $_POST['post_index'] ?? '';

if (empty($topic_id) || $post_index === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Не указан ID темы или индекс сообщения']);
    exit;
}

// Загрузка тем
$topics_file = __DIR__ . '/forum_topics.json';
$topics = [];
if (file_exists($topics_file)) {
    $topics = json_decode(file_get_contents($topics_file), true) ?: [];
}

// Проверка существования темы
if (!isset($topics[$topic_id])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Тема не найдена']);
    exit;
}

// Проверка существования сообщения
if (!isset($topics[$topic_id]['posts'][$post_index])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Сообщение не найдено']);
    exit;
}

// Нельзя удалить первое сообщение (тему)
if ($post_index == 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Нельзя удалить первое сообщение темы']);
    exit;
}

// Удаление сообщения
unset($topics[$topic_id]['posts'][$post_index]);

// Переиндексация массива сообщений
$topics[$topic_id]['posts'] = array_values($topics[$topic_id]['posts']);

// Сохранение обновленных тем
if (file_put_contents($topics_file, json_encode($topics, JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['success' => true, 'message' => 'Сообщение успешно удалено']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении']);
}
?>
