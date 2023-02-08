<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <script src="https://unpkg.com/axios@1.1.2/dist/axios.min.js"></script>
    </head>
    <body class="antialiased">
        <h1>Let's Peppol</h1>
        <p>Welcome to Let's Peppol, Peppol but free!</p>
        <h2>Identities</h2>
        @foreach($identities as $identity)
            Name: <b>{{ $identity['name'] }}</b><br>
            Address: {{ $identity['address'] }}<br>
            City: {{ $identity['city'] }}<br>
            Region: {{ $identity['region'] }}<br>
            Country: {{ $identity['country'] }}<br>
            Zip: {{ $identity['zip'] }}<br>

            @if ($identity['kyc_status'] === 0)
                KYC Status: Pending approval<br>
                <button type="button" onclick="approve("{{ $identity['user_id'] }}")">Approve</button><br>
            @elseif ($identity['kyc_status'] === 1)
                KYC Status: Rejected!<br>
                <button type="button" onclick="approve("{{ $identity['user_id'] }}")">Approve</button><br>
            @else
                KYC Status: Approved!<br>
                Peppol identity: {{ $identity['identifier_scheme'] }}::{{ $identity['identifier_value'] }}<br>
            @endif
            <br><br>
        @endforeach

        <script>
            function approve(id) {
                var body = {
                    user_id: id,
                    kyc_status: 2
                };
                axios.post("{{ route('connectors.letspeppol.admin-update-identity') }}", body, {headers:{'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(response => {
                    alert('Updated! Please refresh this page!');
                });
            }
        </script>
    </body>
</html>
