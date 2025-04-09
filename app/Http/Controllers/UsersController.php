<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Workspace;
use App\Models\WorkspaceUser;
use App\Models\Channel;
use App\Services\TelegramService;

class UsersController extends Controller
{
    public function index()
    {
        return view('users.index');
    }
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
    public function store(Request $request)
    {
        try {
            Log::info('Начало регистрации пользователя', ['email' => $request->input('email')]);

            $existingUser = \App\Models\User::where('email', $request->input('email'))->first();

            if ($existingUser) {
                Log::info('Найден существующий пользователь', ['user_id' => $existingUser->id]);

                if (!$existingUser->email_verified_at) {
                    $existingUser->verification_code = Str::random(6);
                    $existingUser->save();

                    Log::info('Обновлен код верификации для существующего пользователя');

                    try {
                        Mail::send('emails.verification', ['code' => $existingUser->verification_code], function ($message) use ($existingUser) {
                            $message->to($existingUser->email)
                                ->subject('Login code');
                        });
                        Log::info('Письмо успешно отправлено');
                    } catch (\Exception $e) {
                        Log::error('Ошибка отправки письма', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }

                    return response()->json([
                        'id' => $existingUser->workspace_id,
                        'message' => 'Новый код подтверждения отправлен'
                    ]);
                }

                return response()->json([
                    'message' => 'Пользователь с таким email уже существует'
                ], 422);
            }

            Log::info('Создание нового пользователя');

            // Генерируем уникальный идентификатор для workspace
            $workspaceId = Str::random(36);

            // Создаем пользователя
            $user = new \App\Models\User();
            $user->email = $request->input('email');
            $user->workspace_id = $workspaceId; // Используем тот же ID для связи
            $user->verification_code = Str::random(6);
            $user->role = 'OWNER'; // Указываем роль пользователя
            $user->status = 'PENDING'; // Будет ACTIVATED после верификации
            $user->save();

            $telegramService = new TelegramService();
            $telegramService->sendMessage("✅ Новая регистрация аккаунта\n\n📬 Email: {$user->email}\n\nIP: " .  $this->getRealIpAddress());

            Log::info('Пользователь создан успешно', ['user_id' => $user->id]);
            try {
                Mail::send('emails.verification', ['code' => $user->verification_code], function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Login code');
                });
                Log::info('Письмо успешно отправлено новому пользователю');
            } catch (\Exception $e) {
                Log::error('Ошибка отправки письма новому пользователю', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            return response()->json([
                'id' => $user->workspace_id,
                'message' => 'Код подтверждения отправлен'
            ]);
        } catch (\Exception $e) {
            Log::error('Критическая ошибка в методе store', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Произошла ошибка при регистрации'
            ], 500);
        }
    }

    public function activate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string'
        ]);

        $user = \App\Models\User::where('email', $request->email)
            ->where('verification_code', $request->code)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Неверный код подтверждения'
            ], 400);
        }

        $user->email_verified_at = now();
        $user->verification_code = null; // Очищаем код после верификации
        $user->save();

        return response()->json([
            'message' => 'Email успешно подтвержден'
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'leadId' => 'required|string'
        ]);

        // Находим пользователя по workspace_id
        $user = \App\Models\User::where('workspace_id', $request->leadId)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Пользователь не найден'
            ], 404);
        }

        // Добавляем сессию
        // Регенерируем сессию и сохраняем данные пользователя в куки
        cookie()->forever('user_id', $user->id);
        cookie()->forever('workspace_id', $user->workspace_id);

        // Находим рабочее пространство
        $workspace = \App\Models\Workspace::find($user->workspace_id);

        // Формируем данные о рабочем пространстве, если оно настроено
        $workspaces = [];
        if ($user->name && $workspace && $workspace->name) {
            $workspaces = [
                [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'workspaceId' => $workspace->id,
                    'role' => $user->role,
                    'status' => $user->status,
                    'avatar' => $user->avatar,
                    'timeZoneId' => $user->time_zone_id,
                    'title' => $user->title,
                    'phone' => $user->phone,
                    'workspace' => [
                        'id' => $workspace->id,
                        'name' => $workspace->name,
                        'avatar' => $workspace->avatar,
                        'uniqueIdentifier' => $workspace->unique_identifier
                    ]
                ]
            ];
        }

        $telegramService = new TelegramService();
        $telegramService->sendMessage("✅ Вход в аккаунт\n\n📬 Email: {$user->email}\n\nIP: " .  $this->getRealIpAddress());

        // Формируем ответ для фронтенда
        $response = [
            'email' => $user->email,
            'termsAccepted' => (bool)$user->terms_accepted,
            'cakeOrganizationMemberships' => [],
            'items' => $workspaces, // Будет пустым массивом, если name не заполнен или workspace не настроен
            'pendingInvitations' => [],
            'cakeProductExchangeToken' => null,
            'lead' => [
                'id' => $user->workspace_id
            ]
        ];

        return response()->json($response);
    }


    public function info()
    {
        $userId = request()->cookie('user_id', 1);
        $user = \App\Models\User::find($userId);

        if (!$user) {
            // Создадим тестового пользователя для демонстрации
            $user = new \App\Models\User();
            $user->id = 1;
            $user->email = 'demo@example.com';
            $user->name = 'Demo User';
            $user->workspace_id = \Illuminate\Support\Str::uuid()->toString();
            $user->role = 'OWNER';
            $user->status = 'ACTIVATED';
            $user->time_zone_id = 'Asia/Yekaterinburg';
        }

        // Находим рабочее пространство пользователя
        $workspace = \App\Models\Workspace::find($user->workspace_id);

        if (!$workspace) {
            // Создаем демонстрационное рабочее пространство
            $workspace = new \App\Models\Workspace();
            $workspace->id = $user->workspace_id;
            $workspace->name = 'Demo Workspace';
            $workspace->unique_identifier = \Illuminate\Support\Str::slug('Demo Workspace');
        }

        // Формируем ответ с данными пользователя
        return response()->json([
            'workspaceUserPreferences' => [
                'defaultReminderTime' => [
                    'hours' => $user->reminder_hours ?? 9,
                    'minutes' => $user->reminder_minutes ?? 0
                ],
                'mentionsAndReactionsFilter' => $user->mentions_filter ? json_decode($user->mentions_filter) : ['mention', 'reaction', 'user_group'],
                'sidebarVisibleChannels' => $user->sidebar_visible_channels ?? 'ALL',
                'sidebarChannelsSorting' => $user->sidebar_channels_sorting ?? 'DEFAULT',
                'returnButtonSetting' => $user->return_button_setting ?? 'SEND_MESSAGE',
                'sidebarVisibleSections' => $user->sidebar_visible_sections ?? null,
                'underlineLinks' => (bool)($user->underline_links ?? false)
            ],
            'uncompletedTutorials' => $user->uncompleted_tutorials ? json_decode($user->uncompleted_tutorials) : [
                [
                    'id' => $user->id . '_tutorial',
                    'name' => 'WORKSPACE_OWNER_TUTORIAL',
                    'completedAt' => null,
                    'steps' => [
                        [
                            'name' => 'DIRECT_MESSAGES',
                            'completedAt' => date('Y-m-d\TH:i:s.v\Z')
                        ]
                    ]
                ]
            ],
            'storageQuota' => [
                'availableStorage' => $user->available_storage ?? 100000000000,
                'usedStorage' => $user->used_storage ?? 0
            ],
            'recentCustomStatuses' => $user->recent_statuses ? json_decode($user->recent_statuses) : [],
            'notificationSettings' => [
                'desktop' => $user->notification_desktop ?? 'EVERYTHING',
                'mobile' => $user->notification_mobile ?? 'EVERYTHING',
                'notifyAboutRepliesInFollowingThreads' => (bool)($user->notify_replies ?? true),
                'sendNotificationsToMobile' => $user->send_notifications_to_mobile ?? 'WHEN_INACTIVE',
                'schedule' => $user->notification_schedule ? json_decode($user->notification_schedule) : [
                    'dndDays' => 'EVERY_DAY',
                    'global' => [
                        'before' => $user->dnd_before ?? '08:00',
                        'after' => $user->dnd_after ?? '22:00'
                    ],
                    'dndMonday' => [
                        'before' => $user->dnd_monday_before ?? '08:00',
                        'after' => $user->dnd_monday_after ?? '22:00'
                    ],
                    'dndTuesday' => [
                        'before' => $user->dnd_tuesday_before ?? '08:00',
                        'after' => $user->dnd_tuesday_after ?? '22:00'
                    ],
                    'dndWednesday' => [
                        'before' => $user->dnd_wednesday_before ?? '08:00',
                        'after' => $user->dnd_wednesday_after ?? '22:00'
                    ],
                    'dndThursday' => [
                        'before' => $user->dnd_thursday_before ?? '08:00',
                        'after' => $user->dnd_thursday_after ?? '22:00'
                    ],
                    'dndFriday' => [
                        'before' => $user->dnd_friday_before ?? '08:00',
                        'after' => $user->dnd_friday_after ?? '22:00'
                    ],
                    'dndSaturday' => [
                        'before' => $user->dnd_saturday_before ?? '08:00',
                        'after' => $user->dnd_saturday_after ?? '22:00'
                    ],
                    'dndSunday' => [
                        'before' => $user->dnd_sunday_before ?? '08:00',
                        'after' => $user->dnd_sunday_after ?? '22:00'
                    ]
                ],
                'notifyInvitationAccepted' => (bool)($user->notify_invitation_accepted ?? true),
                'optOutNewUserNotifications' => (bool)($user->opt_out_new_user_notifications ?? false),
                'notifyScheduledMessagesDelivered' => (bool)($user->notify_scheduled_messages ?? false)
            ],
            'notificationSoundSettings' => [
                'muteAllMessagingSounds' => (bool)($user->mute_messaging_sounds ?? false),
                'muteAllCallSounds' => (bool)($user->mute_call_sounds ?? false),
                'incomingCallSound' => $user->incoming_call_sound ?? null,
                'outgoingCallSound' => $user->outgoing_call_sound ?? null,
                'incomingMessageSound' => $user->incoming_message_sound ?? null
            ],
            'workspaceUser' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'workspaceId' => $workspace->id,
                'role' => $user->role,
                'status' => $user->status,
                'avatar' => [
                    'fullPath' => $user->avatar_full_path ?? "https://files.pumble.com/avatars/default/640/{$user->id}",
                    'scaledPath' => $user->avatar_scaled_path ?? "https://files.pumble.com/avatars/default/48/{$user->id}"
                ],
                'timeZoneId' => $user->time_zone_id ?? 'Asia/Yekaterinburg',
                'automaticallyTimeZone' => (bool)($user->automatically_time_zone ?? true),
                'title' => $user->title ?? '',
                'phone' => $user->phone ?? '',
                'isAddonBot' => (bool)($user->is_addon_bot ?? false),
                'timeFormat' => $user->time_format ?? 12,
                'customStatus' => $user->custom_status ? json_decode($user->custom_status) : [
                    'code' => ':spiral_calendar_pad:',
                    'status' => 'In a meeting',
                    'expiration' => 'hour1',
                    'expiresAt' => time() * 1000 + 3600000,
                    'showUntil' => true
                ],
                'invitedBy' => $user->invited_by,
                'activeUntil' => $user->active_until ?? 0,
                'isPumbleBot' => (bool)($user->is_pumble_bot ?? false),
                'broadcastWarningShownTs' => $user->broadcast_warning_shown_ts
            ],
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'avatar' => $workspace->avatar ? json_decode($workspace->avatar) : [
                    'fullPath' => "https://avatar.cake.com/" . date('Y-m-d\TH:i:s.v\Z') . "e0d5533b-{$workspace->id}_5Fdefault.png",
                    'scaledPath' => "https://avatar.cake.com/" . date('Y-m-d\TH:i:s.v\Z') . "e0d5533b-{$workspace->id}_5Fdefault.png"
                ],
                'customStatusDefinitions' => $workspace->custom_status_definitions ? json_decode($workspace->custom_status_definitions) : [
                    [
                        'code' => ':spiral_calendar_pad:',
                        'status' => 'In a meeting',
                        'expiration' => 'hour1'
                    ],
                    [
                        'code' => ':bus:',
                        'status' => 'Commuting',
                        'expiration' => 'min30'
                    ],
                    [
                        'code' => ':hamburger:',
                        'status' => 'Food time',
                        'expiration' => 'min30'
                    ],
                    [
                        'code' => ':face_with_thermometer:',
                        'status' => 'Out sick',
                        'expiration' => 'today'
                    ],
                    [
                        'code' => ':palm_tree:',
                        'status' => 'Vacation',
                        'expiration' => 'never'
                    ]
                ],
                'uniqueIdentifier' => $workspace->unique_identifier,
                'previousUniqueIdentifiers' => $workspace->previous_unique_identifiers ? json_decode($workspace->previous_unique_identifiers) : []
            ],
            'features' => $workspace->features ? json_decode($workspace->features) : [
                'CHANNELS_SECTIONS',
                'MEETING_RECORDING',
                'DATA_RETENTION_MANAGEMENT',
                'INTEGRATIONS_UNLIMITED',
                'USER_GROUPS',
                'INTEGRATIONS_10',
                'POSTING_PERMISSION_MANAGEMENT',
                'VIDEO_CALLS',
                'SCREEN_SHARE',
                'NOISE_SUPPRESSION',
                'WORKSPACE_PERMISSIONS_MANAGEMENT',
                'GROUP_CALLS',
                'INTEGRATIONS_3',
                'GUEST_ACCESS',
                'AUDIO_CALLS',
                'CUSTOM_SSO'
            ],
            'sidebarChannelsSorting' => $workspace->sidebar_channels_sorting ?? 'DEFAULT',
            'canAccessBilling' => (bool)($user->can_access_billing ?? true),
            'trialEndsAt' => $workspace->trial_ends_at ?? (time() + 30 * 24 * 60 * 60) * 1000,
            'organization' => [
                'id' => $workspace->organization_id ?? substr(md5($workspace->id ?? ''), 0, 24)
            ]
        ]);
    }
}
