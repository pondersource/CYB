<script src="https://unpkg.com/axios@1.1.2/dist/axios.min.js"></script>
<div>
    <input type="text" id="fname" name="fname" value="username"><br>
    <span onclick="submitAuth{{ $app_code_name }}();">Submit</span>
</div>
<script>
    console.log('here1');
    function submitAuth{{ $app_code_name }}() {
        console.log('here2');
        axios.post('/apps/{{ $app_code_name }}/auth', {}, {headers:{'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(response => {
            alert('Login done?!');
        });
    }
</script>