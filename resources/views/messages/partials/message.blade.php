@php
    $currentUserId = $currentUserId ?? auth()->id();
    $isMine = $message->sender_id === $currentUserId;
@endphp

<div class="group flex {{ $isMine ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
    <div class="max-w-[82%]">
        <div class="rounded-2xl px-4 py-3 shadow-sm {{ $isMine ? 'bg-slate-800 text-white dark:bg-slate-700' : 'bg-white text-gray-900 dark:bg-[#252525] dark:text-gray-100' }}">
            <p class="whitespace-pre-line text-sm leading-relaxed">{{ $message->body }}</p>
            <div class="mt-2 flex items-center gap-2 text-[11px] font-semibold {{ $isMine ? 'text-slate-300' : 'text-gray-400' }}">
                <span>{{ $message->created_at->format('d.m.Y H:i') }}</span>
                @if($isMine)
                    <button type="button"
                            onclick="deleteMessage({{ $message->id }})"
                            class="rounded-full px-2 py-0.5 text-slate-300 opacity-80 transition hover:bg-white/10 hover:text-white sm:opacity-0 sm:group-hover:opacity-100">
                        Șterge
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
