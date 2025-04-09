<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Channel;
use Illuminate\Support\Facades\Log;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{

    public function createWorkspace(Request $request)
    {
            $request->validate([
            'leadId' => 'nullable|string',
            'workspaceName' => 'required|string',
            'timeZoneId' => 'nullable|string',
            'affiliateTid' => 'nullable|string',
            'affiliateRef' => 'nullable|string',
            'seoAnalyticsUserId' => 'nullable|string',
            'seoAnalyticsPageUrl' => 'nullable|string',
            'fullName' => 'nullable|string',
        ]);
        $user = User::where('workspace_id', $request->leadId)->first();
        
        // Создаем рабочее пространство
                $workspace = new Workspace();
        $workspace->id =  $user->workspace_id;
                $workspace->name = $request->workspaceName;
        $workspace->unique_identifier = \Illuminate\Support\Str::slug($request->workspaceName) . '-' . rand(1000, 9999);
        $workspace->custom_status_definitions = [
            [
                "code" => ":spiral_calendar_pad:",
                "status" => "In a meeting",
                "expiration" => "hour1"
            ],
            [
                "code" => ":bus:",
                "status" => "Commuting",
                "expiration" => "min30"
            ],
            [
                "code" => ":hamburger:",
                "status" => "Food time",
                "expiration" => "min30"
            ],
            [
                "code" => ":face_with_thermometer:",
                "status" => "Out sick",
                "expiration" => "today"
            ],
            [
                "code" => ":palm_tree:",
                "status" => "Vacation",
                "expiration" => "never"
            ]
        ];
        $workspace->previous_unique_identifiers = [];
                $workspace->save();
                
        // Создаем канал General
        $generalChannel = new Channel();
        $generalChannel->id = \Illuminate\Support\Str::uuid();
        $generalChannel->name = 'general';
        $generalChannel->description = '';
        $generalChannel->channel_type = 'PUBLIC';
        $generalChannel->creator_id = $user->id;
        $generalChannel->workspace_id = $workspace->id;
        $generalChannel->is_member = true;
        $generalChannel->is_main = true;
        $generalChannel->is_initial = true;
        $generalChannel->save();

        // Создаем канал Random
        $randomChannel = new Channel();
        $randomChannel->id = \Illuminate\Support\Str::uuid();
        $randomChannel->name = 'random';
        $randomChannel->description = '';
        $randomChannel->channel_type = 'PUBLIC';
        $randomChannel->creator_id = $user->id;
        $randomChannel->workspace_id = $workspace->id;
        $randomChannel->is_member = true;
        $randomChannel->is_initial = true;
        $randomChannel->save();

        // Создаем пользователя с полным именем, если оно предоставлено
        if ($request->fullName) {
            if ($user) {
                $user->name = $request->fullName;
                $user->save();
            }
        }
        // Генерируем токены
            $exchangeToken = \Illuminate\Support\Str::random(64);
        $accessToken = \Illuminate\Support\Str::random(64);
            
        // Создаем ответ в формате JSON
            return response()->json([
                'workspace' => [
                    'id' => $workspace->id,
                    'name' => $workspace->name,
                'avatar' => null,
                'customStatusDefinitions' => $workspace->custom_status_definitions,
                    'uniqueIdentifier' => $workspace->unique_identifier,
                    'previousUniqueIdentifiers' => []
                ],
                'workspaceUser' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'workspaceId' => $workspace->id,
                'role' => 'OWNER',
                'status' => 'ACTIVATED',
                    'avatar' => [
                        'fullPath' => "https://files.pumble.com/avatars/default/640/{$user->id}",
                        'scaledPath' => "https://files.pumble.com/avatars/default/48/{$user->id}"
                    ],
                'timeZoneId' => $request->timeZoneId ?? 'Asia/Yekaterinburg',
                'automaticallyTimeZone' => true,
                'title' => '',
                'phone' => '',
                'isAddonBot' => false,
                'timeFormat' => 12,
                'customStatus' => null,
                    'invitedBy' => null,
                    'activeUntil' => 0,
                'isPumbleBot' => false,
                    'broadcastWarningShownTs' => null
                ],
                'exchangeToken' => $exchangeToken,
                'accessToken' => $accessToken
        ]);
    }

    public function workspaceUsers($id)
    {
        // Вариант для реальных данных из базы
        try {
            $workspace = Workspace::find($id);
            if (!$workspace) {
                return response()->json([
                    'message' => 'Рабочее пространство не найдено'
                ], 404);
            }
            
            $users = User::where('workspace_id', $id)->get();
           
            $formattedUsers = $users->map(function ($user) {
                return [
                    "id" => $user->id,
                    "email" => $user->email,
                    "name" => $user->name,
                    "workspaceId" => $user->workspace_id,
                    "role" => $user->role,
                    "status" => $user->status,
                    "avatar" => [
                        "fullPath" => $user->avatar_full_path ?? "https://files.pumble.com/avatars/default/640/{$user->id}",
                        "scaledPath" => $user->avatar_scaled_path ?? "https://files.pumble.com/avatars/default/48/{$user->id}"
                    ],
                    "timeZoneId" => $user->time_zone_id ?? "Asia/Yekaterinburg",
                    "automaticallyTimeZone" => (bool)($user->automatically_time_zone ?? true),
                    "title" => $user->title ?? "",
                    "phone" => $user->phone ?? "",
                    "isAddonBot" => (bool)($user->is_addon_bot ?? false),
                    "timeFormat" => $user->time_format ?? 12,
                    "customStatus" => $user->custom_status ?? null,
                    "invitedBy" => $user->invited_by ?? null,
                    "activeUntil" => $user->active_until ?? 0,
                    "isPumbleBot" => (bool)($user->is_pumble_bot ?? false),
                    "broadcastWarningShownTs" => $user->broadcast_warning_shown_ts ?? null
                ];
            });

            return response()->json($formattedUsers);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Ошибка при получении пользователей рабочего пространства', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Произошла ошибка при получении пользователей рабочего пространства'
            ], 500);
        }
    }

    public function permissions($id)
    {
        try {
            $workspace = \App\Models\Workspace::find($id);
            if (!$workspace) {
                return response()->json([
                    'message' => 'Рабочее пространство не найдено'
                ], 404);
            }

            return response()->json([
                "configuredPermissions" => [
                    "messagePermissions" => [
                        "useChannelAndHereInChannels" => "EVERYONE",
                        "showWarningWhenUsingTags" => "NEVER",
                        "canSendDirectMessages" => "EVERYONE"
                    ],
                    "invitationPermissions" => [
                        "canInvite" => "EVERYONE_EXCEPT_GUESTS"
                    ],
                    "channelManagement" => [
                        "canCreatePrivateChannels" => "EVERYONE_PLUS_MULTI_CHANNEL_GUESTS",
                        "canCreatePublicChannels" => "EVERYONE_EXCEPT_GUESTS",
                        "canArchiveChannel" => "EVERYONE_EXCEPT_GUESTS",
                        "canRemoveMembersFromPrivateChannel" => "EVERYONE_EXCEPT_GUESTS",
                        "canRemoveMembersFromPublicChannel" => "EVERYONE_EXCEPT_GUESTS",
                        "canManagePostingPermissions" => "OWNERS_AND_ADMINS"
                    ],
                    "userGroupsManagement" => [
                        "canCreateAndDisableUserGroups" => "OWNERS_AND_ADMINS",
                        "canModifyUserGroup" => "OWNERS_AND_ADMINS",
                        "createAdminsUserGroup" => false,
                        "createOwnersUserGroup" => false
                    ],
                    "messageEditingPermissions" => [
                        "canEditMessage" => "ANY_TIME",
                        "canDeleteMessage" => "EVERYONE"
                    ],
                    "customEmojiPermissions" => [
                        "canManageCustomEmojis" => "EVERYONE_EXCEPT_GUESTS"
                    ],
                    "appManagement" => [
                        "canInstallApps" => "OWNERS_AND_ADMINS",
                        "canUninstallApps" => "OWNERS_AND_ADMINS",
                        "canRequestInstallations" => "EVERYONE_EXCEPT_GUESTS"
                    ]
                ],
                "messagePermissions" => [
                    "useChannelAndHereInChannels" => "EVERYONE",
                    "showWarningWhenUsingTags" => "NEVER",
                    "canSendDirectMessages" => "EVERYONE"
                ],
                "invitationPermissions" => [
                    "canInvite" => "EVERYONE_EXCEPT_GUESTS"
                ],
                "channelManagement" => [
                    "canCreatePrivateChannels" => "EVERYONE_PLUS_MULTI_CHANNEL_GUESTS",
                    "canCreatePublicChannels" => "EVERYONE_EXCEPT_GUESTS",
                    "canArchiveChannel" => "EVERYONE_EXCEPT_GUESTS",
                    "canRemoveMembersFromPrivateChannel" => "EVERYONE_EXCEPT_GUESTS",
                    "canRemoveMembersFromPublicChannel" => "EVERYONE_EXCEPT_GUESTS",
                    "canManagePostingPermissions" => "OWNERS_AND_ADMINS"
                ],
                "userGroupsManagement" => [
                    "canCreateAndDisableUserGroups" => "OWNERS_AND_ADMINS",
                    "canModifyUserGroup" => "OWNERS_AND_ADMINS",
                    "createAdminsUserGroup" => false,
                    "createOwnersUserGroup" => false
                ],
                "messageEditingPermissions" => [
                    "canEditMessage" => "ANY_TIME",
                    "canDeleteMessage" => "EVERYONE"
                ],
                "customEmojiPermissions" => [
                    "canManageCustomEmojis" => "EVERYONE_EXCEPT_GUESTS"
                ],
                "appManagement" => [
                    "canInstallApps" => "OWNERS_AND_ADMINS",
                    "canUninstallApps" => "OWNERS_AND_ADMINS",
                    "canRequestInstallations" => "EVERYONE_EXCEPT_GUESTS"
                ],
                "callManagementPermissions" => [
                    "reactionsEnabled" => true
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Ошибка при получении разрешений рабочего пространства', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Произошла ошибка при получении разрешений рабочего пространства'
            ], 500);
        }
    }
    public function workspaceUsersChannels($id, $userId)
    {
        // Получаем каналы из базы данных
        $channels = \App\Models\Channel::where('workspace_id', $id)->get();

        // Преобразуем данные в нужный формат
        $result = [];

        foreach ($channels as $channel) {
            
            $result[] = [
                "unread" => 0,
                "mentions" => 0,
                "directChannelParticipants" => [],
                "numberOfUsers" => 1,
                "creatorId" => (string)$channel->creator_id,
                "timestamp" => $channel->created_at->format('Y-m-d\TH:i:s\Z'),
                "timestampMilli" => $channel->created_at->timestamp * 1000,
                "lastMessageTimestamp" => $channel->last_message_timestamp ? date('Y-m-d\TH:i:s\Z', strtotime($channel->last_message_timestamp)) : $channel->created_at->format('Y-m-d\TH:i:s\Z'),
                "lastMessageTimestampMilli" => $channel->last_message_timestamp_milli ?: $channel->created_at->timestamp * 1000,
                "id" => (string)$channel->id,
                "workspaceId" => $channel->workspace_id,
                "channelType" => $channel->channel_type,
                "name" => $channel->name,
                "description" => $channel->description ?? "",
                "isMember" => (bool)$channel->is_member,
                "isMuted" => (bool)$channel->is_muted,
                "isHidden" => (bool)$channel->is_hidden,
                "isArchived" => (bool)$channel->is_archived,
                "isPumbleBot" => false,
                "isAddonBot" => false,
                "lastMarkTimestamp" => $channel->updated_at->format('Y-m-d\TH:i:s.v\Z'),
                "lastMarkTimestampMilli" => $channel->updated_at->timestamp * 1000,
                "isMain" => (bool)$channel->is_main,
                "isInitial" => (bool)$channel->is_initial,
                "sectionId" => $channel->section_id ?? "",
                "postingPermissions" => [
                    "allowThreads" => true,
                    "allowMentions" => true,
                    "postingPermissionsGroup" => "EVERYONE",
                    "workspaceUserIds" => []
                ],
                "desktopNotificationPreferences" => null,
                "mobileNotificationPreferences" => null,
                "notifyAboutRepliesInThreads" => false,
                "addedById" => (string)$channel->creator_id,
                "archivedById" => null
            ];
        }

        return response()->json($result);
    }
    public function workspaceUsersFrequentReactions($id, $userId)
    {
        return response()->json([
            [
                "code" => ":+1:",
                "frequency" => 1
            ],
            [
                "code" => ":slightly_smiling_face:",
                "frequency" => 1
            ],
            [
                "code" => ":heart:",
                "frequency" => 1
            ],
            [
                "code" => ":white_check_mark:",
                "frequency" => 1
            ],
            [
                "code" => ":eyes:",
                "frequency" => 1
            ]
        ]);
    }

    public function workspaceUsersChannelsSections($id, $userId)
    {
        // Для запросов на запланированные сообщения
        if (request()->query('type') === 'messages') {
            return response()->json([
                "scheduledMessages" => []
            ]);
        }

        // Для запросов на секции/разделы сайдбара
        if (request()->query('type') === 'sections' || request()->query('demo') === 'true') {
            return response()->json([
                "sections" => [
                    [
                        "id" => "",
                        "label" => "Channels",
                        "order" => 0,
                        "type" => "CHANNELS_DEFAULT",
                        "icon" => "",
                        "iconCode" => "",
                        "iconSkinTone" => 1,
                        "sidebarVisibleChannels" => "ALL",
                        "sidebarChannelsSorting" => "DEFAULT"
                    ],
                    [
                        "id" => "",
                        "label" => "Direct messages",
                        "order" => 1,
                        "type" => "DMS_DEFAULT",
                        "icon" => "",
                        "iconCode" => "",
                        "iconSkinTone" => 1,
                        "sidebarVisibleChannels" => "ALL",
                        "sidebarChannelsSorting" => "DEFAULT"
                    ],
                    [
                        "id" => "APPS",
                        "label" => "Apps",
                        "order" => 2,
                        "type" => "APPS",
                        "icon" => "",
                        "iconCode" => "",
                        "iconSkinTone" => 1,
                        "sidebarVisibleChannels" => "ALL",
                        "sidebarChannelsSorting" => "DEFAULT"
                    ]
                ]
            ]);
        }

        // По умолчанию возвращаем пустой список запланированных сообщений
        return response()->json([
            "scheduledMessages" => []
        ]);
    }

    public function appsInstallations($workspaceId)
    {
        return response()->json([
            [
                "app" => [
                    "id" => "000000000000000000000000",
                    "name" => "Pumble",
                    "displayName" => "Pumble",
                    "bot" => false,
                    "botTitle" => "Pumble",
                    "scopes" => [
                        "botScopes" => [],
                        "userScopes" => []
                    ],
                    "shortcuts" => [],
                    "slashCommands" => [
                        [
                            "command" => "/status",
                            "description" => "Try anything like 'out', 'lunch', 'call',...",
                            "usageHint" => "[any status] [period of time]"
                        ],
                        [
                            "command" => "/clear-status",
                            "description" => "Clear your custom status",
                            "usageHint" => ""
                        ],
                        [
                            "command" => "/meet",
                            "description" => "Create meeting link",
                            "usageHint" => ""
                        ],
                        [
                            "command" => "/invite",
                            "description" => "Invite users to channel",
                            "usageHint" => "[@someone, ...]"
                        ],
                        [
                            "command" => "/postpone",
                            "description" => "Remind yourself about the last message in a channel",
                            "usageHint" => "[period of time]"
                        ],
                        [
                            "command" => "/shrug",
                            "description" => "Shrug your message",
                            "usageHint" => "[text]"
                        ],
                        [
                            "command" => "/me",
                            "description" => "Displays action text",
                            "usageHint" => "[text]"
                        ],
                        [
                            "command" => "/help",
                            "description" => "More info",
                            "usageHint" => ""
                        ]
                    ],
                    "redirectUrls" => [
                        "https://app.pumble"
                    ],
                    "eventSubscriptions" => [
                        "events" => []
                    ],
                    "blockInteraction" => [
                        "url" => "https://app.pumble"
                    ],
                    "viewAction" => [
                        "url" => null
                    ],
                    "dynamicMenus" => [],
                    "avatar" => [
                        "fullPath" => "https://files.pumble.com/avatars/default/pumble-logo",
                        "scaledPath" => "https://files.pumble.com/avatars/default/pumble-logo"
                    ],
                    "published" => true,
                    "listingUrl" => "/access-request?redirectUrl=https%3A%2F%2Fapp.pumble&clientId=000000000000000000000000&scopes=&defaultWorkspaceId=67f4e81233e2584b4b89d699",
                    "helpUrl" => null,
                    "native" => true
                ],
                "installedBy" => "",
                "botUserId" => "",
                "createdAt" => 1744103442000
            ]
        ]);
    }

    public function messagesV1($id, $channelId)
    {
        // Получаем сообщения из базы данных
        $messages = \App\Models\Message::where('workspace_id', $id)
            ->where('channel_id', $channelId)
            ->orderBy('timestamp_milli', 'desc')
            ->get();

        $formattedMessages = $messages->map(function ($message) {
            return [
                "id" => $message->id,
                "workspaceId" => $message->workspace_id,
                "channelId" => $message->channel_id,
                "author" => $message->author,
                "text" => $message->text,
                "timestamp" => $message->timestamp,
                "timestampMilli" => $message->timestamp_milli,
                "subtype" => $message->subtype ?? "",
                "reactions" => $message->reactions ?? [],
                "linkPreviews" => $message->link_previews ?? [],
                "isFollowing" => $message->is_following ?? false,
                "threadRootInfo" => $message->thread_root_info,
                "threadReplyInfo" => $message->thread_reply_info,
                "files" => $message->files ?? [],
                "deleted" => $message->deleted ?? false,
                "edited" => $message->edited ?? false,
                "localId" => $message->local_id ?? "",
                "attachments" => $message->attachments ?? [],
                "savedTimestampMilli" => $message->saved_timestamp_milli ?? 0,
                "blocks" => $message->blocks,
                "meta" => $message->meta,
                "authorAppId" => $message->author_app_id,
                "systemMessage" => $message->system_message ?? false
            ];
        })->toArray();

        return response()->json([
            "messages" => $formattedMessages,
            "hasMoreBefore" => false,
            "hasMoreAfter" => false
        ]);
    }

    public function createMessage($id, $channelId, $userId, Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'blocks' => 'required|array',
            'blocks.*.type' => 'string',
            'blocks.*.elements' => 'array',
            'blocks.*.elements.*.type' => 'string',
            'blocks.*.elements.*.elements' => 'array',
            'blocks.*.elements.*.elements.*.type' => 'string',
            'blocks.*.elements.*.elements.*.text' => 'string',
            'files' => 'array',
            'pLocalId' => 'required|string',
            'retryNum' => 'integer'
        ]);
        
        // Проверка существования канала
        $channel = \App\Models\Channel::where('id', $channelId)->first();
        if (!$channel) {
            return response()->json([
                'error' => 'Channel not found'
            ], 404);
        }
        
        // Создаем новое сообщение
        $message = new \App\Models\Message();
        $message->id = \Illuminate\Support\Str::uuid();
        $message->workspace_id = $id;
        $message->channel_id = $channelId;
        $message->author = $userId;
        $message->text = $request->text;
        $message->timestamp = now()->toIso8601String();
        $message->timestamp_milli = now()->timestamp * 1000;
        $message->subtype = "";
        $message->reactions = [];
        $message->link_previews = [];
        $message->is_following = false;
        $message->thread_root_info = null;
        $message->thread_reply_info = null;
        $message->files = $request->files ?? [];
        $message->deleted = false;
        $message->edited = false;
        $message->local_id = $request->pLocalId;
        $message->attachments = [];
        $message->saved_timestamp_milli = 0;
        $message->blocks = $request->blocks;
        $message->meta = null;
        $message->author_app_id = null;
        $message->system_message = false;
        $message->save();
        
        // Обновляем timestamp последнего сообщения в канале
        $channel->last_message_timestamp = now()->toIso8601String();
        $channel->last_message_timestamp_milli = now()->timestamp * 1000;
        $channel->save();
        
        return response()->json([
            "id" => $message->id,
            "workspaceId" => $message->workspace_id,
            "channelId" => $message->channel_id,
            "author" => $message->author,
            "text" => $message->text,
            "timestamp" => $message->timestamp,
            "timestampMilli" => $message->timestamp_milli,
            "subtype" => $message->subtype,
            "reactions" => $message->reactions,
            "linkPreviews" => $message->link_previews,
            "isFollowing" => $message->is_following,
            "threadRootInfo" => $message->thread_root_info,
            "threadReplyInfo" => $message->thread_reply_info,
            "files" => $message->files,
            "deleted" => $message->deleted,
            "edited" => $message->edited,
            "localId" => $message->local_id,
            "attachments" => $message->attachments,
            "savedTimestampMilli" => $message->saved_timestamp_milli,
            "blocks" => $message->blocks,
            "meta" => $message->meta,
            "authorAppId" => $message->author_app_id,
            "systemMessage" => $message->system_message
        ]);
        
    }

    public function channel($id, $channelId)
    {
        $channel = Channel::where('id', $channelId)->first();
        
        if (!$channel) {
            return response()->json(['error' => 'Канал не найден'], 404);
        }
        
        $pinnedMessages = [];
        $users = User::where('id', $channel->creator_id)->pluck('id')->toArray();
        
        $channelData = [
            'creatorId' => $channel->creator_id,
            'timestamp' => $channel->timestamp,
            'timestampMilli' => $channel->timestamp_milli,
            'lastMessageTimestamp' => $channel->last_message_timestamp,
            'lastMessageTimestampMilli' => $channel->last_message_timestamp_milli,
            'id' => $channel->id,
            'workspaceId' => $channel->workspace_id,
            'channelType' => $channel->channel_type,
            'name' => $channel->name,
            'description' => $channel->description ?? '',
            'isMember' => $channel->is_member,
            'isMuted' => $channel->is_muted,
            'isHidden' => $channel->is_hidden,
            'isArchived' => $channel->is_archived ?? false,
            'isPumbleBot' => false,
            'isAddonBot' => false,
            'lastMarkTimestamp' => $channel->last_mark_timestamp ?? now()->toIso8601String(),
            'lastMarkTimestampMilli' => $channel->last_mark_timestamp_milli ?? now()->timestamp * 1000,
            'isMain' => $channel->is_main ?? false,
            'isInitial' => $channel->is_initial ?? false,
            'sectionId' => $channel->section_id ?? '',
            'postingPermissions' => [
                'allowThreads' => true,
                'allowMentions' => true,
                'postingPermissionsGroup' => 'EVERYONE',
                'workspaceUserIds' => []
            ],
            'desktopNotificationPreferences' => null,
            'mobileNotificationPreferences' => null,
            'notifyAboutRepliesInThreads' => false,
            'addedById' => $channel->added_by_id ?? $channel->creator_id,
            'archivedById' => $channel->archived_by_id
        ];
        
        return response()->json([
            'channel' => $channelData,
            'users' => $users,
            'pinnedMessages' => $pinnedMessages,
            'homeView' => null
        ]);
    }

    public function retention($id, $channelId)
    {
        return response()->json([
            'retention' => [
                'workspacePolicy' => [
                    'policy' => 'KEEP',
                    'deleteInDays' => null
                ],
                'workspaceAllowsOverride' => false,
                'policy' => null
            ]
        ]);
    }

    public function inAppNews($id, $userId)
    {
        return response()->json([
            'inAppNews' => [
                [
                    "id" => "67d960d73120b6e2c34a8816",
                    "title" => "NEW: Emoji reactions in Meetings!",
                    "content" => "Express yourself in meetings with our new Meeting reactions. Send floating emoji reactions to engage with everyone in real time.",
                    "isRead" => false,
                    "url" => "#",
                    "timestamp" => "2025-03-18T01:00:00Z",
                    "timestampMilli" => 1742259600000
                ],
                [
                    "id" => "67c5a3163120b6e2c3ac5e98",
                    "title" => "NEW: Your video calls, now twice as big!",
                    "content" => "Host up to 50 participants on PRO and 100 on BUSINESS, ENTERPRISE & CAKE.com BUNDLE plan. Enjoy extra space for your team to connect!",
                    "isRead" => false,
                    "url" => "#",
                    "timestamp" => "2025-03-03T01:00:00Z",
                    "timestampMilli" => 1740963600000
                ]
            ]
        ]);
    }

    public function drafts($id, $userId)
    {
        return response()->json([
            'drafts' => []
        ]);
    }

    public function onlineUsers($workspaceId)
    {
        // Получаем пользователей из базы данных для данного рабочего пространства
        $users = User::where('workspace_id', $workspaceId)->get();
        
        // Формируем массив ID пользователей
        $userIds = $users->pluck('id')->toArray();
        
        return response()->json([
            'onlineUsers' => $userIds
        ]);
    }


    public function dndInfo($workspaceId)
    {
        // Получаем пользователей из базы данных или другого источника
        $users = User::where('workspace_id', $workspaceId)->get();
        
        $response = [];
        
        foreach ($users as $user) {
            $response[] = [
                "workspaceUserId" => $user->id,
                "dnd" => [
                    [
                        time(), // текущее время
                        time() + 3600 // текущее время + 1 час
                    ],
                    [
                        strtotime('tomorrow 9:00'), // завтра 9:00
                        strtotime('tomorrow 17:00') // завтра 17:00
                    ],
                    [
                        strtotime('next monday 9:00'), // следующий понедельник 9:00
                        strtotime('next monday 17:00') // следующий понедельник 17:00
                    ]
                ]
            ];
        }
        
        // Если пользователей нет, возвращаем тестовые данные
        if (empty($response)) {
            return response()->json([
                [
                    "workspaceUserId" => "67f5532676b70322ad51ae30",
                    "dnd" => [
                        [
                            1744052400,
                            1744081200
                        ],
                        [
                            1744131600,
                            1744167600
                        ]
                    ]
                ],
                [
                    "workspaceUserId" => "67f5532676b70322ad51ae2e",
                    "dnd" => [
                        [
                            1744052400,
                            1744081200
                        ],
                        [
                            1744131600,
                            1744167600
                        ]
                    ]
                ]
            ]);
        }
        
        return response()->json($response);
    }

}