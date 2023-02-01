<?php

namespace App\Connectors\LetsPeppol\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_CREDIT_NOTE = 'credit-note';
    public const TYPE_ORDER = 'order';
    public const TYPE_ORDER_RESPONSE = 'order-response';
    public const TYPE_DESPATCH_ADVICE = 'despatch-advice';

    public const DIRECTION_INCOMING = 0;
    public const DIRECTION_OUTGOING = 1;

    protected $table = 'lp_messages';

    protected $fillable =
        ['user_id', 'identity_id', 'registrar', 'reference', 'type', 'direction', 'file_name'];

    use HasFactory;
}
