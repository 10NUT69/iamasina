<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Service;
use App\Rules\WithoutContactDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('account.index', ['tab' => 'mesaje']);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => $this->unreadMessagesCount($request->user()->id),
        ]);
    }

    public function poll(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless($conversation->isParticipant($user), 403);
        abort_if($conversation->isHiddenFor($user), 404);

        $afterId = max(0, (int) $request->query('after_id', 0));

        $newMessages = $conversation->messages()
            ->with('sender')
            ->where('id', '>', $afterId)
            ->orderBy('created_at')
            ->get();

        Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'unread_count' => $this->unreadMessagesCount($user->id),
            'messages' => $newMessages->map(fn (Message $message) => [
                'id' => $message->id,
                'html' => view('messages.partials.message', [
                    'message' => $message,
                    'currentUserId' => $user->id,
                ])->render(),
            ])->values(),
        ]);
    }

    public function startFromService(Request $request, Service $service)
    {
        $user = $request->user();
        $seller = $service->user;

        abort_unless($seller, 404);

        if ($seller->id === $user->id) {
            return back()->with('error', 'Nu poți trimite mesaj către propriul anunț.');
        }

        $body = $this->validatedBody($request);

        $conversation = DB::transaction(function () use ($service, $seller, $user, $body) {
            $conversation = Conversation::query()->firstOrCreate(
                [
                    'service_id' => $service->id,
                    'buyer_id' => $user->id,
                    'seller_id' => $seller->id,
                ],
                ['last_message_at' => now()]
            );

            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'body' => $body,
            ]);

            $conversation->forceFill([
                'last_message_at' => $message->created_at,
                'buyer_deleted_at' => null,
                'seller_deleted_at' => null,
            ])->save();

            return $conversation;
        });

        return redirect()
            ->route('account.index', ['tab' => 'mesaje', 'conversation' => $conversation->id])
            ->with('success', 'Mesajul a fost trimis.');
    }

    public function store(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless($conversation->isParticipant($user), 403);
        abort_if($conversation->isHiddenFor($user), 404);

        $body = $this->validatedBody($request);

        DB::transaction(function () use ($conversation, $user, $body) {
            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'body' => $body,
            ]);

            $conversation->forceFill([
                'last_message_at' => $message->created_at,
                'buyer_deleted_at' => null,
                'seller_deleted_at' => null,
            ])->save();
        });

        return redirect()->route('account.index', ['tab' => 'mesaje', 'conversation' => $conversation->id]);
    }

    public function destroyMessage(Request $request, Message $message)
    {
        abort_unless($message->sender_id === $request->user()->id, 403);

        $conversation = $message->conversation;
        $message->delete();

        if ($conversation) {
            $conversation->forceFill([
                'last_message_at' => $conversation->messages()->latest('created_at')->value('created_at'),
            ])->save();
        }

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return back();
    }

    public function destroyConversation(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->isParticipant($request->user()), 403);

        $conversation->hideFor($request->user());

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()
            ->route('account.index', ['tab' => 'mesaje'])
            ->with('success', 'Conversația a fost ștearsă din contul tău.');
    }

    private function validatedBody(Request $request): string
    {
        $validated = $request->validate([
            'body' => ['bail', 'required', 'string', 'max:2000', new WithoutContactDetails],
        ], [
            'body.required' => 'Scrie un mesaj înainte de trimitere.',
        ]);

        return trim($validated['body']);
    }

    private function unreadMessagesCount(int $userId): int
    {
        return Message::query()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->whereHas('conversation', fn ($query) => $query
                ->where(fn ($participantQuery) => $participantQuery
                    ->where('buyer_id', $userId)
                    ->whereNull('buyer_deleted_at'))
                ->orWhere(fn ($participantQuery) => $participantQuery
                    ->where('seller_id', $userId)
                    ->whereNull('seller_deleted_at')))
            ->count();
    }
}
