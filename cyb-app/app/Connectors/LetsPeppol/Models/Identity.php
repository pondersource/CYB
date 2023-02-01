<?php

namespace App\Connectors\LetsPeppol\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;

class Identity extends Model
{
    public const KYC_STATUS_PENDING_APPROVAL = 0;
    public const KYC_STATUS_REJECTED = 1;
    public const KYC_STATUS_APPROVED = 2;

    protected $table = 'lp_identities';

    // Having auth_id means update notifier is on
    protected $fillable = [
        'user_id', 'auth_id', 'name', 'address', 'city', 'region', 'country', 'zip', 'kyc_status',
        'identifier_scheme', 'identifier_value', 'registrar', 'reference'
    ];

    use HasFactory;
}
