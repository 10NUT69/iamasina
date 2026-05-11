<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['conversation_id', 'id'], 'messages_conversation_id_id_index');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->index(['buyer_id', 'buyer_deleted_at', 'last_message_at'], 'conv_buyer_visible_last_msg_index');
            $table->index(['seller_id', 'seller_deleted_at', 'last_message_at'], 'conv_seller_visible_last_msg_index');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_conversation_id_id_index');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conv_buyer_visible_last_msg_index');
            $table->dropIndex('conv_seller_visible_last_msg_index');
        });
    }
};
