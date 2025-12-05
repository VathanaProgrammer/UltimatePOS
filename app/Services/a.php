<?php

$update = json_decode(file_get_contents('php://input'), true);

if (isset($update['message'])) {
    $chat = $update['message']['chat'];
    \Log::info('Group Chat ID: '.$chat['id'].' | Type: '.$chat['type'].' | Title: '.$chat['title'] ?? 'No title');
}