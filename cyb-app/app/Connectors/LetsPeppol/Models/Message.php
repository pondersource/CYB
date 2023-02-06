<?php

namespace App\Connectors\LetsPeppol\Models;

use App\Core\DataType\Invoice\Invoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model implements Invoice
{
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_CREDIT_NOTE = 'credit-note';
    public const TYPE_ORDER = 'order';
    public const TYPE_ORDER_RESPONSE = 'order-response';
    public const TYPE_DESPATCH_ADVICE = 'despatch-advice';

    public const DIRECTION_INCOMING = Invoice::DIRECTION_INCOMING;
    public const DIRECTION_OUTGOING = Invoice::DIRECTION_OUTGOING;
    
    public const STORAGE_BASE_PATH = __DIR__.'/../messages/';

    protected $table = 'lp_messages';

    protected $fillable = [
        'user_id', 'identity_id', 'registrar', 'reference', 'type', 'direction',
        'file_name', 'receive_time'
    ];

    use HasFactory;

    public function getDirection(): int
    {
        return $this['direction'];
    }

    public function getContent(): string
    {
        return file_get_contents(self::STORAGE_BASE_PATH.$message['file_name']);
    }
}
