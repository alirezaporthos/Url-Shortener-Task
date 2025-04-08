<?php


$app = require_once __DIR__ . '/../src/bootstrap.php';


header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);


$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');


// Route handling
try {
    switch ($path) {
        case 'api/auth/register':
            if ($method === 'POST') {
                $data = parseJsonInput();
                echo json_encode($authController->register($data));
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
        break;
        case 'api/auth/login':
            if ($method === 'POST') {
                $data = parseJsonInput();
                echo json_encode($authController->login($data));
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
        case 'api/urls':

            // Check user authentication
            $userData = $authMiddleware->handle();
            
            if ($method === 'POST') {
                $data = parseJsonInput();
                echo json_encode($urlController->create($userData['user_id'], $data['url']));
            } else if ($method === 'GET') {
                echo json_encode($urlController->getUserUrls($userData['user_id']));
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
        case 'hello':
            echo 'hello world';
            break;
        case (preg_match('/^api\/urls\/(\d+)$/', $path, $matches) ? true : false):
            // Authenticate user
            $userData = $authMiddleware->handle();
            $urlId = (int)$matches[1];

            switch ($method) {
                case 'PUT':
                    $data = parseJsonInput();
                    $result = $urlController->update($urlId, $userData['user_id'], $data);
                    header('Content-Type: application/json');
                    if (!$result['success']) {
                        http_response_code(400);
                    }
                    echo json_encode($result);
                    break;

                case 'DELETE':
                    $result = $urlController->delete($urlId, $userData['user_id']);
                    header('Content-Type: application/json');
                    if (!$result['success']) {
                        http_response_code(404);
                    }
                    echo json_encode($result);
                    break;

                default:
                    http_response_code(405);
                    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
    
        default:
        if (preg_match('/^[a-zA-Z0-9]{6}$/', $path)) {
            $result = $urlController->getByShortCode($path);
            header('Content-Type: application/json');
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'original_url' => $result['data']['original_url'],
                        'short_code' => $path
                    ]
                ]);
            } else {
                http_response_code(404);
                echo json_encode($result);
            }
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Not found']);
        }
        break;    
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} 


function parseJsonInput() {
    $input = file_get_contents('php://input');
    if (empty($input)) {
        return [];
    }
    
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data: ' . json_last_error_msg()
        ]);
        exit;
    }
    
    return $data;
}

