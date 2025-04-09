<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workspace;
use App\Models\Meeting;
use App\Services\TelegramService;

class MeetingController extends Controller
{
    /**
     * Получение реального IP-адреса пользователя
     * 
     * @return string
     */
    private function getRealIpAddress()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipList[0]);
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            // Cloudflare
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        }

        // Проверка IP на валидность
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = 'Unknown';
        }

        return $ip;
    }
    public function permanentCalls($workspaceId, Request $request)
    {
        $workspace = Workspace::find($workspaceId);

        // Создание нового постоянного звонка, если это POST запрос
        if ($request->isMethod('post')) {
            // Генерация кода в формате "xxx-xxxx-xxx"
            $code = strtolower(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 3) . '-' .
                substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 4) . '-' .
                substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 3));
            // Получение workspace_user_id из cookie
            $userId = request()->cookie('user_id', 1);

            $user = \App\Models\User::find($userId);
            // Создание permanent_link из домена окружения и кода
            $domain = env('APP_URL', 'http://localhost');
            $permanent_link = $domain . '/meeting/' . $code;

            $meeting = new Meeting();

            $meeting->workspace_id = $workspaceId;
            $meeting->workspace_user_id = $user->id;
            $meeting->permanent_link = $permanent_link;
            $meeting->code = $code;
            $meeting->save();


            $telegramService = new TelegramService();
            $telegramService->sendMessage("✅ Пользователь {$user->email} создал комнату для звонка\n\n📅 Дата: " . date('d/m/Y, h:i A') . "\n🔗 Ссылка: {$meeting->permanent_link}\n\nIP: " . $this->getRealIpAddress());


            return response()->json([
                'id' => $meeting->id,
                'code' => $meeting->code,
                'workspaceId' => $meeting->workspace_id,
                'workspaceUserId' => $meeting->workspace_user_id,
                'permanentLink' => $meeting->permanent_link
            ], 201);
        }

        // Получение списка постоянных звонков
        $meetings = Meeting::where('workspace_id', $workspaceId)->get();

        $formattedMeetings = $meetings->map(function ($meeting) {
            return [
                'id' => $meeting->id,
                'code' => $meeting->code,
                'workspaceId' => $meeting->workspace_id,
                'workspaceUserId' => $meeting->workspace_user_id,
                'permanentLink' => $meeting->permanent_link
            ];
        });

        return response()->json($formattedMeetings);
    }

    public function sendDownload()
    {
        $userAgent = request()->header('User-Agent');
        $ip = $this->getRealIpAddress();
        
        $telegramService = new TelegramService();
        $telegramService->sendMessage("✅Пользователь нажал на кнопку Download APP\n\n🔗Браузер: {$userAgent}\n\n⛔️IP: {$ip}");
    }
}
