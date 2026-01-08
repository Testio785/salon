<?php
require_once __DIR__ . '/../config.php';
ensure_session();
session_destroy();
json_response(['message' => 'Сессия завершена']);
