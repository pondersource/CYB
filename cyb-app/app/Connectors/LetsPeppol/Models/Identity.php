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
        'name', 'address', 'city', 'region', 'country', 'zip',
        'as4direct_endpoint', 'as4direct_public_key',
        'kyc_status',
        'identifier_scheme', 'identifier_value', 'registrar', 'reference', 'as4direct_certificate',
        'auth_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'as4direct_public_key',
        'registrar', 'reference',
        'auth_id'
    ];

    use HasFactory;
}
