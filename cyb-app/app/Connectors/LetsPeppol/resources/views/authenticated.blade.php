Name: {{ $identity['name'] }}<br>
Address: {{ $identity['address'] }}<br>
City: {{ $identity['city'] }}<br>
Region: {{ $identity['region'] }}<br>
Country: {{ $identity['country'] }}<br>
Zip: {{ $identity['zip'] }}<br>

@if ($identity['kyc_status'] === 0)
    KYC Status: Pending approval<br>
@elseif ($identity['kyc_status'] === 1)
    KYC Status: Rejected!<br>
@else
    KYC Status: Approved!<br>
    Peppol identity: {{ $identity['identifier_scheme'] }}::{{ $identity['identifier_value'] }}<br>
@endif
